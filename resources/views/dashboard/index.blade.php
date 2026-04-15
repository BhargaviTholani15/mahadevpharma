<x-layouts.app title="Dashboard">
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
        <p class="text-gray-500 mt-1">Welcome back, {{ auth()->user()->full_name }}</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-blue-50"><svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m10.5 1.5 3 3L5.5 12.5l-3-3z"/><path d="m13.5 4.5 3 3"/><path d="m2.5 9.5 3 3"/></svg></div>
            <div><p class="text-sm text-gray-500">Products</p><p class="text-2xl font-bold text-gray-900">{{ number_format($stats['products']) }}</p></div>
        </div>
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-green-50"><svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></div>
            <div><p class="text-sm text-gray-500">Clients</p><p class="text-2xl font-bold text-gray-900">{{ number_format($stats['clients']) }}</p></div>
        </div>
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-yellow-50"><svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.66-7.88H5.12"/></svg></div>
            <div><p class="text-sm text-gray-500">Pending Orders</p><p class="text-2xl font-bold text-gray-900">{{ number_format($stats['orders_pending']) }}</p></div>
        </div>
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-emerald-50"><svg class="w-6 h-6 text-emerald-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h12"/><path d="M6 8h12"/><path d="m6 13 8.5 8"/><path d="M6 13h3"/><path d="M9 13c6.667 0 6.667-10 0-10"/></svg></div>
            <div><p class="text-sm text-gray-500">Monthly Revenue</p><p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['revenue_month'], 2) }}</p></div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-purple-50"><svg class="w-6 h-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M18 17V9"/><path d="M13 17V5"/><path d="M8 17v-3"/></svg></div>
            <div><p class="text-sm text-gray-500">Orders Today</p><p class="text-2xl font-bold text-gray-900">{{ $stats['orders_today'] }}</p></div>
        </div>
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-red-50"><svg class="w-6 h-6 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg></div>
            <div><p class="text-sm text-gray-500">Outstanding</p><p class="text-2xl font-bold text-gray-900">₹{{ number_format($stats['outstanding'], 2) }}</p></div>
        </div>
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-orange-50"><svg class="w-6 h-6 text-orange-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></div>
            <div><p class="text-sm text-gray-500">Low Stock</p><p class="text-2xl font-bold text-gray-900">{{ $stats['low_stock'] }}</p></div>
        </div>
        <div class="stat-card">
            <div class="p-3 rounded-xl bg-pink-50"><svg class="w-6 h-6 text-pink-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/></svg></div>
            <div><p class="text-sm text-gray-500">Expiring Soon</p><p class="text-2xl font-bold text-gray-900">{{ $stats['expiring_soon'] }}</p></div>
        </div>
    </div>

    {{-- Recent orders --}}
    <div class="card">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Orders</h3>
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th>Order #</th><th>Client</th><th>Amount</th><th>Status</th><th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                    <tr>
                        <td><a href="{{ route('orders.show', $order) }}" class="text-green-600 hover:underline font-medium">{{ $order->order_number }}</a></td>
                        <td>{{ $order->client?->business_name ?? '-' }}</td>
                        <td>₹{{ number_format($order->total_amount, 2) }}</td>
                        <td><x-status-badge :status="$order->status" /></td>
                        <td>{{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-8 text-gray-400">No orders yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-layouts.app>
