<x-layouts.app title="Products">
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Products</h1><p class="text-gray-500 mt-1">Manage your product catalog</p></div>
        <a href="{{ route('products.create') }}" class="btn-primary"><svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg> Add Product</a>
    </div>

    {{-- Filters --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div class="flex-1 min-w-[200px]">
                <label class="form-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..." class="form-input">
            </div>
            <div class="w-48">
                <label class="form-label">Brand</label>
                <select name="brand_id" class="form-select">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)<option value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach
                </select>
            </div>
            <div class="w-48">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">All Categories</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary">Filter</button>
            @if(request()->hasAny(['search','brand_id','category_id']))
            <a href="{{ route('products.index') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead class="bg-gray-50/50"><tr><th>Name</th><th>SKU</th><th>Brand</th><th>Category</th><th>MRP</th><th>Selling</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($products as $p)
                    <tr>
                        <td><a href="{{ route('products.show', $p) }}" class="font-medium text-gray-900 hover:text-green-600">{{ $p->name }}</a><br><span class="text-xs text-gray-400">{{ $p->generic_name }}</span></td>
                        <td class="font-mono text-xs">{{ $p->sku }}</td>
                        <td>{{ $p->brand?->name ?? '-' }}</td>
                        <td>{{ $p->category?->name ?? '-' }}</td>
                        <td>{{ $p->mrp ? '₹'.number_format($p->mrp, 2) : '-' }}</td>
                        <td>{{ $p->selling_price ? '₹'.number_format($p->selling_price, 2) : '-' }}</td>
                        <td><x-status-badge :status="$p->is_active ? 'ACTIVE' : 'INACTIVE'" /></td>
                        <td>
                            <div class="flex gap-1">
                                <a href="{{ route('products.edit', $p) }}" class="btn-secondary btn-sm">Edit</a>
                                <form method="POST" action="{{ route('products.destroy', $p) }}" onsubmit="return confirm('Delete this product?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No products found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
</x-layouts.app>
