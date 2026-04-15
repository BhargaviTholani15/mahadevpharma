<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\LedgerEntry;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::with(['client:id,business_name']);

        $user = $request->user();
        if ($user->isClient()) {
            $query->where('client_id', $user->client->id);
        } elseif ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($method = $request->get('payment_method')) {
            $query->where('payment_method', $method);
        }

        if ($from = $request->get('from_date')) {
            $query->whereDate('payment_date', '>=', $from);
        }

        if ($to = $request->get('to_date')) {
            $query->whereDate('payment_date', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('business_name', 'like', "%{$search}%"));
            });
        }

        $payments = $query->latest('payment_date')->paginate($request->get('per_page', 25));

        return response()->json($payments);
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['client', 'allocations.invoice:id,invoice_number,grand_total,balance_due']);

        return response()->json(['payment' => $payment]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:CASH,UPI,BANK_TRANSFER,CHEQUE,PAYMENT_GATEWAY',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'allocations' => 'nullable|array',
            'allocations.*.invoice_id' => 'required_with:allocations|exists:invoices,id',
            'allocations.*.amount' => 'required_with:allocations|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $payment = Payment::create([
                'payment_number' => Payment::generatePaymentNumber(),
                'client_id' => $validated['client_id'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => $validated['payment_date'],
                'reference_number' => $validated['reference_number'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => 'CONFIRMED',
                'received_by' => $request->user()->id,
                'confirmed_by' => $request->user()->id,
                'confirmed_at' => now(),
            ]);

            // Allocate to invoices
            $allocatedTotal = 0;
            if (!empty($validated['allocations'])) {
                foreach ($validated['allocations'] as $alloc) {
                    $invoice = Invoice::lockForUpdate()->findOrFail($alloc['invoice_id']);

                    if ($alloc['amount'] > $invoice->balance_due) {
                        throw new \Exception("Allocation exceeds balance due on invoice {$invoice->invoice_number}");
                    }

                    PaymentAllocation::create([
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'amount' => $alloc['amount'],
                    ]);

                    $invoice->update([
                        'amount_paid' => $invoice->amount_paid + $alloc['amount'],
                        'balance_due' => $invoice->balance_due - $alloc['amount'],
                        'status' => ($invoice->balance_due - $alloc['amount']) <= 0 ? 'PAID' : 'PARTIALLY_PAID',
                    ]);

                    $allocatedTotal += $alloc['amount'];
                }
            }

            // Create ledger entry (credit = payment received)
            $client = Client::lockForUpdate()->findOrFail($validated['client_id']);
            $lastEntry = LedgerEntry::where('client_id', $client->id)->latest('id')->first();
            $runningBalance = ($lastEntry?->running_balance ?? 0) - $validated['amount'];

            LedgerEntry::create([
                'client_id' => $client->id,
                'entry_date' => $validated['payment_date'],
                'entry_type' => 'PAYMENT',
                'debit_amount' => 0,
                'credit_amount' => $validated['amount'],
                'running_balance' => $runningBalance,
                'reference_type' => 'payment',
                'reference_id' => $payment->id,
                'narration' => "Payment {$payment->payment_number} via {$validated['payment_method']}",
                'financial_year' => LedgerEntry::currentFinancialYear(),
                'created_by' => $request->user()->id,
            ]);

            $client->update(['current_outstanding' => max(0, $runningBalance)]);

            ActivityLog::log('payment.recorded', 'payment', $payment->id);

            return response()->json(['payment' => $payment->load('allocations')], 201);
        });
    }
}
