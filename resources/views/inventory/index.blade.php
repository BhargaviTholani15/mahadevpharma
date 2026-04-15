<x-layouts.app title="Inventory">
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Inventory</h1><p class="text-gray-500 mt-1">Stock overview, batches, and movements</p></div>
        <a href="{{ route('inventory.create-batch') }}" class="btn-primary">+ Add Batch</a>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 bg-gray-100 rounded-xl p-1 w-fit">
        @foreach(['stocks' => 'Stock Overview', 'batches' => 'Batches', 'movements' => 'Movements', 'low-stock' => 'Low Stock', 'expiry' => 'Expiring Soon'] as $key => $label)
        <a href="{{ route('inventory.index', ['tab' => $key]) }}" class="px-4 py-2 rounded-lg text-sm font-medium transition {{ $tab === $key ? 'bg-white shadow text-gray-900' : 'text-gray-600 hover:text-gray-900' }}">{{ $label }}</a>
        @endforeach
    </div>

    {{-- Search --}}
    <div class="card"><form method="GET" class="flex gap-3"><input type="hidden" name="tab" value="{{ $tab }}"><div class="flex-1"><input type="text" name="search" value="{{ $search }}" placeholder="Search..." class="form-input"></div><button type="submit" class="btn-primary">Search</button></form></div>

    @if($tab === 'stocks')
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Total Stock</th><th>Batches</th><th>Status</th></tr></thead>
            <tbody>
                @forelse($items as $p)
                @php $stock = $p->activeBatches->sum(fn($b) => $b->warehouseStocks->sum('quantity')); @endphp
                <tr>
                    <td class="font-medium text-gray-900">{{ $p->name }}</td>
                    <td class="font-semibold {{ $stock > 50 ? 'text-green-600' : ($stock > 0 ? 'text-yellow-600' : 'text-red-600') }}">{{ $stock }}</td>
                    <td>{{ $p->activeBatches->count() }}</td>
                    <td><x-status-badge :status="$stock > 50 ? 'ACTIVE' : ($stock > 0 ? 'PENDING' : 'EXPIRED')" /></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-gray-400">No products found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
    @elseif($tab === 'batches')
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Batch #</th><th>Expiry</th><th>MRP</th><th>Stock</th></tr></thead>
            <tbody>
                @forelse($items as $b)
                <tr>
                    <td class="font-medium">{{ $b->product?->name ?? '-' }}</td>
                    <td class="font-mono text-xs">{{ $b->batch_number }}</td>
                    <td class="{{ $b->expiry_date && $b->expiry_date->lt(now()->addDays(90)) ? 'text-red-600 font-semibold' : '' }}">{{ $b->expiry_date?->format('d M Y') }}</td>
                    <td>₹{{ number_format($b->mrp, 2) }}</td>
                    <td>{{ $b->warehouseStocks->sum('quantity') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No batches found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
    @elseif($tab === 'movements')
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Batch</th><th>Type</th><th>Qty</th><th>Reason</th><th>By</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($items as $m)
                <tr>
                    <td>{{ $m->product?->name ?? '-' }}</td>
                    <td class="font-mono text-xs">{{ $m->batch?->batch_number ?? '-' }}</td>
                    <td><span class="{{ $m->type === 'IN' ? 'badge-green' : 'badge-red' }}">{{ $m->type }}</span></td>
                    <td class="font-semibold">{{ $m->quantity }}</td>
                    <td>{{ $m->reason }}</td>
                    <td>{{ $m->user?->full_name ?? '-' }}</td>
                    <td>{{ $m->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">No movements found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
    @elseif($tab === 'low-stock')
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Current Stock</th><th>Min Level</th><th>Reorder Level</th></tr></thead>
            <tbody>
                @forelse($items as $p)
                @php $stock = $p->activeBatches->sum(fn($b) => $b->warehouseStocks->sum('quantity')); @endphp
                <tr>
                    <td class="font-medium text-gray-900">{{ $p->name }}</td>
                    <td class="text-red-600 font-semibold">{{ $stock }}</td>
                    <td>{{ $p->min_stock_level ?? 0 }}</td>
                    <td>{{ $p->reorder_level ?? 0 }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-gray-400">No low stock items</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
    @elseif($tab === 'expiry')
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Product</th><th>Batch #</th><th>Expiry Date</th><th>Days Left</th><th>Stock</th></tr></thead>
            <tbody>
                @forelse($items as $b)
                @php $daysLeft = now()->diffInDays($b->expiry_date, false); @endphp
                <tr>
                    <td class="font-medium">{{ $b->product?->name ?? '-' }}</td>
                    <td class="font-mono text-xs">{{ $b->batch_number }}</td>
                    <td class="text-red-600 font-semibold">{{ $b->expiry_date->format('d M Y') }}</td>
                    <td class="{{ $daysLeft < 30 ? 'text-red-600' : 'text-yellow-600' }} font-semibold">{{ round($daysLeft) }} days</td>
                    <td>{{ $b->warehouseStocks->sum('quantity') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No expiring batches</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>
    @endif
</div>
</x-layouts.app>
