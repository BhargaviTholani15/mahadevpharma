<x-layouts.app title="Orders">
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Orders</h1><p class="text-gray-500 mt-1">Manage customer orders</p></div>
        @if(!auth()->user()->isClient())<a href="{{ route('orders.create') }}" class="btn-primary">+ New Order</a>@endif
    </div>
    <div class="card"><form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]"><label class="form-label">Search</label><input type="text" name="search" value="{{ request('search') }}" placeholder="Order # or client..." class="form-input"></div>
        <div class="w-40"><label class="form-label">Status</label><select name="status" class="form-select"><option value="">All</option>@foreach(['PENDING','APPROVED','PACKED','OUT_FOR_DELIVERY','DELIVERED','CANCELLED'] as $s)<option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
        <div class="w-40"><label class="form-label">From</label><input type="date" name="from" value="{{ request('from') }}" class="form-input"></div>
        <div class="w-40"><label class="form-label">To</label><input type="date" name="to" value="{{ request('to') }}" class="form-input"></div>
        <button type="submit" class="btn-primary">Apply</button>@if(request()->hasAny(['search','status','from','to']))<a href="{{ url()->current() }}" class="btn-secondary">Clear</a>@endif
    </form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Order #</th><th>Client</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($orders as $o)
                <tr>
                    <td><a href="{{ auth()->user()->isClient() ? route('vendor.orders.show', $o) : route('orders.show', $o) }}" class="font-medium text-green-600 hover:underline">{{ $o->order_number }}</a></td>
                    <td>{{ $o->client?->business_name ?? '-' }}</td>
                    <td>₹{{ number_format($o->total_amount, 2) }}</td>
                    <td><x-status-badge :status="$o->status" /></td>
                    <td>{{ $o->created_at->format('d M Y') }}</td>
                    <td><a href="{{ auth()->user()->isClient() ? route('vendor.orders.show', $o) : route('orders.show', $o) }}" class="btn-secondary btn-sm">View</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No orders found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
    </div>
</div>
</x-layouts.app>
