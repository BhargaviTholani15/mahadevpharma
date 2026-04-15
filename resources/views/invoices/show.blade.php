<x-layouts.app :title="'Invoice ' . $invoice->invoice_number">
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4"><a href="{{ url()->previous() }}" class="btn-secondary btn-sm">&larr; Back</a><h1 class="text-2xl font-bold text-gray-900">{{ $invoice->invoice_number }}</h1><x-status-badge :status="$invoice->status" /></div>
        <a href="{{ route('invoices.pdf', $invoice) }}" class="btn-primary">Download PDF</a>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Invoice Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Client</dt><dd>{{ $invoice->client?->business_name }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Order</dt><dd>{{ $invoice->order?->order_number ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd>{{ $invoice->created_at->format('d M Y') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Due Date</dt><dd>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</dd></div>
            </dl>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Amounts</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>₹{{ number_format($invoice->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tax</dt><dd>₹{{ number_format(($invoice->cgst_total ?? 0) + ($invoice->sgst_total ?? 0) + ($invoice->igst_total ?? 0), 2) }}</dd></div>
                <div class="flex justify-between"><dt class="font-semibold text-gray-900">Total</dt><dd class="text-lg font-bold">₹{{ number_format($invoice->grand_total, 2) }}</dd></div>
                <div class="flex justify-between border-t pt-2"><dt class="text-gray-500">Amount Paid</dt><dd class="text-green-600">₹{{ number_format($invoice->amount_paid, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="font-semibold text-red-600">Balance Due</dt><dd class="text-red-600 font-bold">₹{{ number_format($invoice->balance_due, 2) }}</dd></div>
            </dl>
        </div>
    </div>
    @if($invoice->items && $invoice->items->count())
    <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Line Items</h3>
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Qty</th><th>Rate</th><th>Tax</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr><td>{{ $item->product?->name ?? $item->product_name }}</td><td>{{ $item->quantity }}</td><td>₹{{ number_format($item->unit_price, 2) }}</td><td>₹{{ number_format($item->tax_amount, 2) }}</td><td class="font-semibold">₹{{ number_format($item->total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
</x-layouts.app>
