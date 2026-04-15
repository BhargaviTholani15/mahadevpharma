<x-layouts.app :title="'Order ' . $order->order_number">
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4"><a href="{{ url()->previous() }}" class="btn-secondary btn-sm">&larr; Back</a><h1 class="text-2xl font-bold text-gray-900">{{ $order->order_number }}</h1><x-status-badge :status="$order->status" /></div>
        @if(!auth()->user()->isClient() && $order->status === 'PENDING')
        <div class="flex gap-2">
            <form method="POST" action="{{ route('orders.approve', $order) }}">@csrf<button class="btn-primary btn-sm">Approve</button></form>
            <form method="POST" action="{{ route('orders.reject', $order) }}" onsubmit="return confirm('Reject this order?')">@csrf<button class="btn-danger btn-sm">Reject</button></form>
        </div>
        @endif
        @if(!auth()->user()->isClient() && !in_array($order->status, ['CANCELLED', 'DELIVERED']))
        <form method="POST" action="{{ route('orders.update-status', $order) }}" class="flex gap-2">
            @csrf @method('PATCH')
            <select name="status" class="form-select w-48">
                @foreach(['PENDING','APPROVED','PACKED','OUT_FOR_DELIVERY','DELIVERED','CANCELLED'] as $s)<option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach
            </select>
            <button class="btn-primary btn-sm">Update</button>
        </form>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Order Details</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Client</dt><dd>{{ $order->client?->business_name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Created By</dt><dd>{{ $order->createdBy?->full_name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Date</dt><dd>{{ $order->created_at->format('d M Y H:i') }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Payment Method</dt><dd>{{ $order->payment_method ?? '-' }}</dd></div>
                @if($order->notes)<div><dt class="text-gray-500 mb-1">Notes</dt><dd>{{ $order->notes }}</dd></div>@endif
            </dl>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Totals</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Subtotal</dt><dd>₹{{ number_format($order->subtotal, 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Tax (CGST + SGST)</dt><dd>₹{{ number_format(($order->cgst_total ?? 0) + ($order->sgst_total ?? 0) + ($order->igst_total ?? 0), 2) }}</dd></div>
                <div class="flex justify-between border-t pt-2 mt-2"><dt class="font-semibold text-gray-900">Total</dt><dd class="text-lg font-bold text-green-600">₹{{ number_format($order->total_amount, 2) }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Order Items</h3>
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
                @foreach($order->items as $item)
                <tr><td class="font-medium">{{ $item->product?->name ?? '-' }}</td><td>{{ $item->quantity }}</td><td>₹{{ number_format($item->unit_price, 2) }}</td><td class="font-semibold">₹{{ number_format($item->line_total, 2) }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
</x-layouts.app>
