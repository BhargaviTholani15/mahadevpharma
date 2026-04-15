<x-layouts.app title="Client Dashboard">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Welcome, {{ auth()->user()->full_name }}</h1><p class="text-gray-500 mt-1">Your pharmacy partner dashboard</p></div>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="stat-card"><div class="p-3 rounded-xl bg-blue-50"><svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.66-7.88H5.12"/></svg></div><div><p class="text-sm text-gray-500">Total Orders</p><p class="text-2xl font-bold">{{ $stats['orders_total'] }}</p></div></div>
        <div class="stat-card"><div class="p-3 rounded-xl bg-yellow-50"><svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div><div><p class="text-sm text-gray-500">Pending Orders</p><p class="text-2xl font-bold">{{ $stats['orders_pending'] }}</p></div></div>
        <div class="stat-card"><div class="p-3 rounded-xl bg-red-50"><svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg></div><div><p class="text-sm text-gray-500">Outstanding</p><p class="text-2xl font-bold">₹{{ number_format($stats['outstanding'], 2) }}</p></div></div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="{{ route('vendor.place-order') }}" class="card hover:shadow-md transition group">
            <div class="flex items-center gap-4"><div class="p-4 rounded-xl bg-green-50 group-hover:bg-green-100 transition"><svg class="w-8 h-8 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.66-7.88H5.12"/></svg></div>
            <div><h3 class="text-lg font-semibold text-gray-900">Place Order</h3><p class="text-sm text-gray-500">Browse catalog and order medicines</p></div></div>
        </a>
        <a href="{{ route('vendor.orders') }}" class="card hover:shadow-md transition group">
            <div class="flex items-center gap-4"><div class="p-4 rounded-xl bg-blue-50 group-hover:bg-blue-100 transition"><svg class="w-8 h-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg></div>
            <div><h3 class="text-lg font-semibold text-gray-900">My Orders</h3><p class="text-sm text-gray-500">Track your order history</p></div></div>
        </a>
    </div>
    @if($recentOrders->count())
    <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Recent Orders</h3>
        <table class="data-table w-full"><thead class="bg-gray-50/50"><tr><th>Order #</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>@foreach($recentOrders as $o)<tr><td><a href="{{ route('vendor.orders.show', $o) }}" class="text-green-600 hover:underline">{{ $o->order_number }}</a></td><td>₹{{ number_format($o->total_amount, 2) }}</td><td><x-status-badge :status="$o->status" /></td><td>{{ $o->created_at->format('d M Y') }}</td></tr>@endforeach</tbody>
        </table>
    </div>
    @endif
</div>
</x-layouts.app>
