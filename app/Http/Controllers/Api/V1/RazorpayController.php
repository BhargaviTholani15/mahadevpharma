<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RazorpayController extends Controller
{
    public function createOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'required|numeric|min:1',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Create Razorpay order
        $razorpayKeyId = config('services.razorpay.key_id', env('RAZORPAY_KEY_ID'));
        $razorpaySecret = config('services.razorpay.key_secret', env('RAZORPAY_KEY_SECRET'));

        try {
            $response = Http::withBasicAuth($razorpayKeyId, $razorpaySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => (int) round($validated['amount'] * 100), // paise
                    'currency' => 'INR',
                    'receipt' => $order->order_number,
                    'notes' => [
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'client_id' => $order->client_id,
                    ],
                ]);

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Failed to create Razorpay order',
                    'error' => $response->json(),
                ], 500);
            }

            $razorpayOrder = $response->json();

            return response()->json([
                'razorpay_order_id' => $razorpayOrder['id'],
                'razorpay_key_id' => $razorpayKeyId,
                'amount' => $validated['amount'],
                'currency' => 'INR',
                'order_number' => $order->order_number,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Payment gateway error: ' . $e->getMessage()], 500);
        }
    }

    public function verifyPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'razorpay_payment_id' => 'required|string',
            'razorpay_order_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $razorpaySecret = config('services.razorpay.key_secret', env('RAZORPAY_KEY_SECRET'));

        // Verify signature
        $expectedSignature = hash_hmac(
            'sha256',
            $validated['razorpay_order_id'] . '|' . $validated['razorpay_payment_id'],
            $razorpaySecret
        );

        if ($expectedSignature !== $validated['razorpay_signature']) {
            return response()->json(['message' => 'Payment verification failed — invalid signature'], 400);
        }

        return DB::transaction(function () use ($validated, $request) {
            $order = Order::findOrFail($validated['order_id']);
            $client = Client::lockForUpdate()->findOrFail($order->client_id);

            // Find the invoice for this order
            $invoice = Invoice::where('order_id', $order->id)->first();

            // Create payment record
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'client_id' => $order->client_id,
                'amount' => $validated['amount'],
                'payment_method' => 'PAYMENT_GATEWAY',
                'payment_date' => now()->toDateString(),
                'reference_number' => $validated['razorpay_payment_id'],
                'gateway_txn_id' => $validated['razorpay_payment_id'],
                'status' => 'CONFIRMED',
                'notes' => 'Razorpay payment for ' . $order->order_number,
                'received_by' => $request->user()->id,
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            // Allocate to invoice if exists
            if ($invoice) {
                $allocAmount = min($validated['amount'], $invoice->balance_due);
                PaymentAllocation::create([
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $allocAmount,
                ]);

                $invoice->update([
                    'amount_paid' => $invoice->amount_paid + $allocAmount,
                    'balance_due' => $invoice->balance_due - $allocAmount,
                    'status' => ($invoice->balance_due - $allocAmount) <= 0 ? 'PAID' : 'PARTIALLY_PAID',
                ]);
            }

            // Ledger entry
            $lastEntry = LedgerEntry::where('client_id', $client->id)->latest('id')->first();
            $runningBalance = ($lastEntry?->running_balance ?? 0) - $validated['amount'];

            LedgerEntry::create([
                'client_id' => $client->id,
                'entry_date' => now()->toDateString(),
                'entry_type' => 'PAYMENT',
                'debit_amount' => 0,
                'credit_amount' => $validated['amount'],
                'running_balance' => $runningBalance,
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'narration' => "Online payment {$payment->payment_number} via Razorpay (Ref: {$validated['razorpay_payment_id']})",
                'financial_year' => LedgerEntry::currentFinancialYear(),
                'created_by' => $request->user()->id,
            ]);

            $client->update(['current_outstanding' => max(0, $runningBalance)]);

            return response()->json([
                'message' => 'Payment verified and recorded successfully',
                'payment' => $payment,
            ]);
        });
    }
}
