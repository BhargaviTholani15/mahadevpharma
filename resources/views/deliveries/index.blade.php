<x-layouts.app title="Deliveries">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Deliveries</h1><p class="text-gray-500 mt-1">Track delivery assignments</p></div>
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Order</th><th>Client</th><th>Agent</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($deliveries as $d)
                <tr>
                    <td class="font-medium">{{ $d->order?->order_number ?? '-' }}</td>
                    <td>{{ $d->order?->client?->business_name ?? '-' }}</td>
                    <td>{{ $d->deliveryAgent?->name ?? '-' }}</td>
                    <td><x-status-badge :status="$d->status ?? 'PENDING'" /></td>
                    <td>{{ $d->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No deliveries found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $deliveries->links() }}</div>
    </div>
</div>
</x-layouts.app>
