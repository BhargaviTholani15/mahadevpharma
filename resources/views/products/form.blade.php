<x-layouts.app :title="$product ? 'Edit Product' : 'Add Product'">
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('products.index') }}" class="btn-secondary btn-sm">&larr; Back</a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $product ? 'Edit Product' : 'Add Product' }}</h1>
    </div>

    @if($errors->any())
    <div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ $product ? route('products.update', $product) : route('products.store') }}">
        @csrf
        @if($product) @method('PUT') @endif

        {{-- Basic info --}}
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Basic Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><label class="form-label">Product Name *</label><input type="text" name="name" value="{{ old('name', $product?->name) }}" required class="form-input"></div>
                <div><label class="form-label">Generic Name</label><input type="text" name="generic_name" value="{{ old('generic_name', $product?->generic_name) }}" class="form-input"></div>
                <div><label class="form-label">SKU *</label><input type="text" name="sku" value="{{ old('sku', $product?->sku) }}" required class="form-input"></div>
                <div><label class="form-label">Barcode</label><input type="text" name="barcode" value="{{ old('barcode', $product?->barcode) }}" class="form-input"></div>
                <div><label class="form-label">Brand</label>
                    <select name="brand_id" class="form-select"><option value="">Select brand</option>@foreach($brands as $b)<option value="{{ $b->id }}" {{ old('brand_id', $product?->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">Category</label>
                    <select name="category_id" class="form-select"><option value="">Select category</option>@foreach($categories as $c)<option value="{{ $c->id }}" {{ old('category_id', $product?->category_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">HSN Code *</label>
                    <select name="hsn_code_id" required class="form-select"><option value="">Select HSN</option>@foreach($hsnCodes as $h)<option value="{{ $h->id }}" {{ old('hsn_code_id', $product?->hsn_code_id) == $h->id ? 'selected' : '' }}>{{ $h->code }} — {{ Str::limit($h->description, 40) }}</option>@endforeach</select>
                </div>
            </div>
        </div>

        {{-- Pharmaceutical --}}
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Pharmaceutical Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><label class="form-label">Dosage Form</label>
                    <select name="dosage_form" class="form-select"><option value="">Select</option>@foreach($dosageForms as $d)<option value="{{ $d->name }}" {{ old('dosage_form', $product?->dosage_form) == $d->name ? 'selected' : '' }}>{{ $d->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">Strength</label>
                    <select name="strength" class="form-select"><option value="">Select</option>@foreach($strengths as $s)<option value="{{ $s->name }}" {{ old('strength', $product?->strength) == $s->name ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">Pack Size</label>
                    <select name="pack_size" class="form-select"><option value="">Select</option>@foreach($packSizes as $ps)<option value="{{ $ps->name }}" {{ old('pack_size', $product?->pack_size) == $ps->name ? 'selected' : '' }}>{{ $ps->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">Drug Schedule</label>
                    <select name="schedule_type" class="form-select"><option value="">Select</option>@foreach($drugSchedules as $ds)<option value="{{ $ds->name }}" {{ old('schedule_type', $product?->schedule_type) == $ds->name ? 'selected' : '' }}>{{ $ds->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">Storage Conditions</label>
                    <select name="storage_conditions" class="form-select"><option value="">Select</option>@foreach($storageConditions as $sc)<option value="{{ $sc->name }}" {{ old('storage_conditions', $product?->storage_conditions) == $sc->name ? 'selected' : '' }}>{{ $sc->name }}</option>@endforeach</select>
                </div>
                <div><label class="form-label">Shelf Life (months)</label><input type="number" name="shelf_life_months" value="{{ old('shelf_life_months', $product?->shelf_life_months) }}" class="form-input"></div>
                <div class="md:col-span-2 lg:col-span-3"><label class="form-label">Composition</label><textarea name="composition" class="form-textarea" rows="2">{{ old('composition', $product?->composition) }}</textarea></div>
                <div><label class="form-label">Route of Administration</label>
                    <select name="route_of_administration" class="form-select"><option value="">Select</option>@foreach(['Oral', 'Topical', 'Injectable', 'Inhalation', 'Sublingual', 'Rectal', 'Nasal', 'Ophthalmic', 'Otic'] as $r)<option value="{{ $r }}" {{ old('route_of_administration', $product?->route_of_administration) == $r ? 'selected' : '' }}>{{ $r }}</option>@endforeach</select>
                </div>
                <div class="flex items-center gap-6 md:col-span-2 lg:col-span-3">
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_prescription_only" value="1" {{ old('is_prescription_only', $product?->is_prescription_only) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600"> Prescription Only</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_controlled" value="1" {{ old('is_controlled', $product?->is_controlled) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600"> Controlled Substance</label>
                    <label class="flex items-center gap-2"><input type="checkbox" name="is_returnable" value="1" {{ old('is_returnable', $product?->is_returnable ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600"> Returnable</label>
                </div>
            </div>
        </div>

        {{-- Manufacturer --}}
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Manufacturer & Regulatory</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><label class="form-label">Manufacturer Name</label><input type="text" name="manufacturer_name" value="{{ old('manufacturer_name', $product?->manufacturer_name) }}" class="form-input"></div>
                <div><label class="form-label">Country of Origin</label><input type="text" name="country_of_origin" value="{{ old('country_of_origin', $product?->country_of_origin ?? 'India') }}" class="form-input"></div>
                <div><label class="form-label">Marketing Authorization</label><input type="text" name="marketing_authorization" value="{{ old('marketing_authorization', $product?->marketing_authorization) }}" class="form-input"></div>
                <div class="md:col-span-2 lg:col-span-3"><label class="form-label">Manufacturer Address</label><textarea name="manufacturer_address" class="form-textarea" rows="2">{{ old('manufacturer_address', $product?->manufacturer_address) }}</textarea></div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Pricing</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><label class="form-label">MRP (₹)</label><input type="number" step="0.01" name="mrp" value="{{ old('mrp', $product?->mrp) }}" class="form-input"></div>
                <div><label class="form-label">Purchase Price (₹)</label><input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product?->purchase_price) }}" class="form-input"></div>
                <div><label class="form-label">Selling Price (₹)</label><input type="number" step="0.01" name="selling_price" value="{{ old('selling_price', $product?->selling_price) }}" class="form-input"></div>
                <div><label class="form-label">PTR (₹)</label><input type="number" step="0.01" name="ptr" value="{{ old('ptr', $product?->ptr) }}" class="form-input" placeholder="Price to Retailer"></div>
                <div><label class="form-label">PTS (₹)</label><input type="number" step="0.01" name="pts" value="{{ old('pts', $product?->pts) }}" class="form-input" placeholder="Price to Stockist"></div>
                <div><label class="form-label">Margin %</label><input type="number" step="0.01" name="margin_pct" value="{{ old('margin_pct', $product?->margin_pct) }}" class="form-input"></div>
            </div>
        </div>

        {{-- Inventory --}}
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Inventory Settings</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div><label class="form-label">Min Stock Level</label><input type="number" name="min_stock_level" value="{{ old('min_stock_level', $product?->min_stock_level ?? 0) }}" class="form-input"></div>
                <div><label class="form-label">Reorder Level</label><input type="number" name="reorder_level" value="{{ old('reorder_level', $product?->reorder_level ?? 0) }}" class="form-input"></div>
                <div><label class="form-label">Reorder Quantity</label><input type="number" name="reorder_quantity" value="{{ old('reorder_quantity', $product?->reorder_quantity ?? 0) }}" class="form-input"></div>
                <div><label class="form-label">Lead Time (days)</label><input type="number" name="lead_time_days" value="{{ old('lead_time_days', $product?->lead_time_days) }}" class="form-input"></div>
                <div><label class="form-label">Rack Location</label><input type="text" name="rack_location" value="{{ old('rack_location', $product?->rack_location) }}" class="form-input" placeholder="e.g., A1-S3"></div>
                <div><label class="form-label">Near Expiry Alert (days)</label><input type="number" name="near_expiry_alert_days" value="{{ old('near_expiry_alert_days', $product?->near_expiry_alert_days ?? 90) }}" class="form-input"></div>
                <div><label class="form-label">Batch Prefix</label><input type="text" name="batch_prefix" value="{{ old('batch_prefix', $product?->batch_prefix) }}" class="form-input" placeholder="e.g., BT"></div>
            </div>
        </div>

        {{-- Description & Additional Info --}}
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Description & Additional Info</h3>
            <div class="space-y-4">
                <div><label class="form-label">Description</label><textarea name="description" class="form-textarea" rows="3">{{ old('description', $product?->description) }}</textarea></div>
                <div><label class="form-label">Usage Instructions</label><textarea name="usage_instructions" class="form-textarea" rows="2" placeholder="Dosage and administration instructions">{{ old('usage_instructions', $product?->usage_instructions) }}</textarea></div>
                <div><label class="form-label">Side Effects</label><textarea name="side_effects" class="form-textarea" rows="2" placeholder="Known side effects and warnings">{{ old('side_effects', $product?->side_effects) }}</textarea></div>
                <div><label class="form-label">Image URL</label><input type="url" name="image_url" value="{{ old('image_url', $product?->image_url) }}" class="form-input" placeholder="https://..."></div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="btn-primary">{{ $product ? 'Update Product' : 'Create Product' }}</button>
            <a href="{{ route('products.index') }}" class="btn-secondary">Cancel</a>
        </div>
    </form>
</div>
</x-layouts.app>
