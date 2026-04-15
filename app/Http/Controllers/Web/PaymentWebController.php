<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\LedgerEntry;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['client:id,business_name']);
        if (auth()->user()->isClient()) {
            $query->where('client_id', auth()->user()->client?->id);
        }
        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('payment_number', 'like', "%{$search}%")
                ->orWhereHas('client', fn($c) => $c->where('business_name', 'like', "%{$search}%")));
        }
        if ($from = $request->get('from')) $query->whereDate('payment_date', '>=', $from);
        if ($to = $request->get('to')) $query->whereDate('payment_date', '<=', $to);

        $payments = $query->latest('payment_date')->paginate(25)->withQueryString();
        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $clients = Client::orderBy('business_name')->get(['id', 'business_name']);
        return view('payments.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:CASH,BANK_TRANSFER,UPI,CHEQUE,PAYMENT_GATEWAY',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $paymentNumber = 'PAY-' . now()->format('Ymd') . '-' . str_pad(Payment::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'client_id' => $v['client_id'],
            'payment_number' => $paymentNumber,
            'amount' => $v['amount'],
            'payment_method' => $v['payment_method'],
            'payment_date' => now(),
            'reference_number' => $v['reference_number'] ?? null,
            'notes' => $v['notes'] ?? null,
            'status' => 'COMPLETED',
            'received_by' => auth()->id(),
        ]);

        $lastBalance = LedgerEntry::where('client_id', $v['client_id'])->latest()->value('running_balance') ?? 0;

        LedgerEntry::create([
            'client_id' => $v['client_id'],
            'type' => 'CREDIT',
            'amount' => $v['amount'],
            'running_balance' => $lastBalance - $v['amount'],
            'description' => "Payment received — {$paymentNumber}",
            'reference_type' => 'payment',
            'reference_id' => $payment->id,
        ]);

        return redirect()->route('payments.index')->with('success', 'Payment recorded.');
    }
}
