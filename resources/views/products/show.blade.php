<x-layouts.app :title="$product->name">
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('products.index') }}" class="btn-secondary btn-sm">&larr; Back</a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <x-status-badge :status="$product->is_active ? 'ACTIVE' : 'INACTIVE'" />
        </div>
        <a href="{{ route('products.edit', $product) }}" class="btn-primary">Edit</a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Basic Info</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">SKU</dt><dd class="font-mono">{{ $product->sku }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Generic Name</dt><dd>{{ $product->generic_name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Brand</dt><dd>{{ $product->brand?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd>{{ $product->category?->name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">HSN Code</dt><dd>{{ $product->hsnCode?->code ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Barcode</dt><dd>{{ $product->barcode ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Pharmaceutical</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Dosage Form</dt><dd>{{ $product->dosage_form ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Strength</dt><dd>{{ $product->strength ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Pack Size</dt><dd>{{ $product->pack_size ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Schedule</dt><dd>{{ $product->schedule_type ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Storage</dt><dd>{{ $product->storage_conditions ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Shelf Life</dt><dd>{{ $product->shelf_life_months ? $product->shelf_life_months.' months' : '-' }}</dd></div>
            </dl>
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Pricing</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">MRP</dt><dd class="font-semibold">{{ $product->mrp ? '₹'.number_format($product->mrp, 2) : '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Purchase Price</dt><dd>{{ $product->purchase_price ? '₹'.number_format($product->purchase_price, 2) : '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Selling Price</dt><dd>{{ $product->selling_price ? '₹'.number_format($product->selling_price, 2) : '-' }}</dd></div>
            </dl>
        </div>
        <div class="card">
            <h3 class="font-semibold text-gray-900 mb-4">Stock</h3>
            @php $totalStock = $product->activeBatches->sum(fn($b) => $b->warehouseStocks->sum('quantity')); @endphp
            <p class="text-3xl font-bold {{ $totalStock > 0 ? 'text-green-600' : 'text-red-600' }}">{{ $totalStock }} units</p>
            <p class="text-sm text-gray-500 mt-1">{{ $product->activeBatches->count() }} active batches</p>
        </div>
    </div>

    @if($product->activeBatches->count())
    <div class="card">
        <h3 class="font-semibold text-gray-900 mb-4">Active Batches</h3>
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Batch #</th><th>Expiry</th><th>MRP</th><th>Stock</th></tr></thead>
            <tbody>
                @foreach($product->activeBatches as $batch)
                <tr>
                    <td class="font-mono text-xs">{{ $batch->batch_number }}</td>
                    <td>{{ $batch->expiry_date?->format('d M Y') }}</td>
                    <td>₹{{ number_format($batch->mrp, 2) }}</td>
                    <td>{{ $batch->warehouseStocks->sum('quantity') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
</x-layouts.app>
