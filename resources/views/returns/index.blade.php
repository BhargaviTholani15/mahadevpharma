<x-layouts.app title="Returns">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Returns</h1><p class="text-gray-500 mt-1">Sales return requests</p></div>
    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search return #..." class="form-input"></div><button type="submit" class="btn-primary">Search</button></form></div>
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Return #</th><th>Client</th><th>Order</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($returns as $r)
                <tr>
                    <td class="font-medium">{{ $r->return_number }}</td>
                    <td>{{ $r->client?->business_name ?? '-' }}</td>
                    <td>{{ $r->order?->order_number ?? '-' }}</td>
                    <td>₹{{ number_format($r->total_amount ?? 0, 2) }}</td>
                    <td><x-status-badge :status="$r->status" /></td>
                    <td>{{ $r->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No returns found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $returns->links() }}</div>
    </div>
</div>
</x-layouts.app>
