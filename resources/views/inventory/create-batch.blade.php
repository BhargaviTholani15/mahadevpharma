<x-layouts.app title="Add Batch">
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('inventory.index', ['tab' => 'batches']) }}" class="btn-secondary btn-sm">&larr; Back</a>
        <h1 class="text-2xl font-bold text-gray-900">Add New Batch</h1>
    </div>
    @if($errors->any())<div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-700 text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <form method="POST" action="{{ route('inventory.store-batch') }}" class="card space-y-4">
        @csrf
        <div><label class="form-label">Product *</label>
            <select name="product_id" required class="form-select"><option value="">Select product</option>@foreach($products as $p)<option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }} ({{ $p->sku }})</option>@endforeach</select>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="form-label">Batch Number *</label><input type="text" name="batch_number" value="{{ old('batch_number') }}" required class="form-input"></div>
            <div><label class="form-label">Warehouse *</label>
                <select name="warehouse_id" required class="form-select"><option value="">Select</option>@foreach($warehouses as $w)<option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>@endforeach</select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div><label class="form-label">Mfg Date</label><input type="date" name="mfg_date" value="{{ old('mfg_date') }}" class="form-input"></div>
            <div><label class="form-label">Expiry Date *</label><input type="date" name="expiry_date" value="{{ old('expiry_date') }}" required class="form-input"></div>
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div><label class="form-label">Purchase Price (₹) *</label><input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price') }}" required class="form-input"></div>
            <div><label class="form-label">MRP (₹) *</label><input type="number" step="0.01" name="mrp" value="{{ old('mrp') }}" required class="form-input"></div>
            <div><label class="form-label">Selling Price (₹) *</label><input type="number" step="0.01" name="selling_price" value="{{ old('selling_price') }}" required class="form-input"></div>
        </div>
        <div><label class="form-label">Quantity *</label><input type="number" name="quantity" value="{{ old('quantity') }}" required min="1" class="form-input w-48"></div>
        <div class="flex gap-3 pt-2"><button type="submit" class="btn-primary">Add Batch</button><a href="{{ route('inventory.index') }}" class="btn-secondary">Cancel</a></div>
    </form>
</div>
</x-layouts.app>
