<x-layouts.app title="Purchase Orders">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Purchase Orders</h1><p class="text-gray-500 mt-1">Manage supplier purchase orders</p></div>
    <div class="card"><form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]"><label class="form-label">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="PO number..." class="form-input"></div>
        <div class="w-40"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(['DRAFT','PENDING','APPROVED','ORDERED','PARTIAL','RECEIVED','CANCELLED'] as $s)<option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
        <button type="submit" class="btn-primary">Apply</button>@if(request()->hasAny(['search','status']))<a href="{{ url()->current() }}" class="btn-secondary">Clear</a>@endif
    </form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>PO Number</th><th>Supplier</th><th>Warehouse</th><th>Status</th><th>Subtotal</th><th>Tax</th><th>Total</th><th>Expected Delivery</th><th>Created By</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($purchaseOrders as $po)
                <tr>
                    <td class="font-medium">{{ $po->po_number }}</td>
                    <td>{{ $po->supplier?->name ?? '-' }}</td>
                    <td>{{ $po->warehouse?->name ?? '-' }} {{ $po->warehouse?->code ? '('.$po->warehouse->code.')' : '' }}</td>
                    <td><x-status-badge :status="$po->status" /></td>
                    <td>₹{{ number_format($po->subtotal ?? 0, 2) }}</td>
                    <td>₹{{ number_format($po->tax_amount ?? 0, 2) }}</td>
                    <td>₹{{ number_format($po->total_amount ?? 0, 2) }}</td>
                    <td>{{ $po->expected_delivery_date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ $po->createdBy?->full_name ?? '-' }}</td>
                    <td>{{ $po->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="10" class="text-center py-8 text-gray-400">No purchase orders found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $purchaseOrders->links() }}</div>
    </div>
</div>
</x-layouts.app>
