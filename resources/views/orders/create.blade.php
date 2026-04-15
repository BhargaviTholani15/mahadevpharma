<x-layouts.app title="New Order">
<div class="max-w-4xl mx-auto space-y-6" x-data="orderForm()">
    <div class="flex items-center gap-4"><a href="{{ route('orders.index') }}" class="btn-secondary btn-sm">&larr; Back</a><h1 class="text-2xl font-bold text-gray-900">Create New Order</h1></div>
    @if($errors->any())<div class="rounded-xl p-4 bg-red-50 border border-red-200 text-red-700 text-sm"><ul class="list-disc pl-4">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('orders.store') }}">
        @csrf
        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Client & Payment</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="form-label">Client *</label>
                    <select name="client_id" required class="form-select"><option value="">Select client</option>@foreach($clients as $c)<option value="{{ $c->id }}">{{ $c->business_name }}</option>@endforeach</select></div>
                <div><label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select"><option value="CREDIT">Credit</option><option value="COD">COD</option></select></div>
            </div>
        </div>

        <div class="card mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Order Items</h3>
            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-3 mb-3 items-end">
                    <div class="col-span-5"><label x-show="index === 0" class="form-label">Product</label>
                        <select :name="'items[' + index + '][product_id]'" x-model="item.product_id" @change="setPrice(index)" required class="form-select">
                            <option value="">Select</option>
                            @foreach($products as $p)<option value="{{ $p->id }}" data-price="{{ $p->selling_price ?? $p->mrp ?? 0 }}">{{ $p->name }} ({{ $p->sku }})</option>@endforeach
                        </select>
                    </div>
                    <div class="col-span-2"><label x-show="index === 0" class="form-label">Qty</label><input type="number" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" required class="form-input"></div>
                    <div class="col-span-3"><label x-show="index === 0" class="form-label">Unit Price (₹)</label><input type="number" step="0.01" :name="'items[' + index + '][unit_price]'" x-model.number="item.unit_price" required class="form-input"></div>
                    <div class="col-span-2 flex items-end gap-2">
                        <span class="text-sm font-semibold mb-2" x-text="'₹' + (item.quantity * item.unit_price).toFixed(2)"></span>
                        <button type="button" @click="items.splice(index, 1)" x-show="items.length > 1" class="text-red-500 hover:text-red-700 mb-2">&times;</button>
                    </div>
                </div>
            </template>
            <button type="button" @click="items.push({product_id: '', quantity: 1, unit_price: 0})" class="btn-secondary btn-sm mt-2">+ Add Item</button>
            <div class="mt-4 pt-4 border-t text-right"><span class="text-lg font-bold text-gray-900">Total: ₹<span x-text="total.toFixed(2)">0.00</span></span></div>
        </div>

        <div class="card mb-6"><label class="form-label">Notes</label><textarea name="notes" class="form-textarea" rows="2"></textarea></div>
        <div class="flex gap-3"><button type="submit" class="btn-primary">Create Order</button><a href="{{ route('orders.index') }}" class="btn-secondary">Cancel</a></div>
    </form>
</div>

@push('scripts')
<script>
function orderForm() {
    return {
        items: [{product_id: '', quantity: 1, unit_price: 0}],
        get total() { return this.items.reduce((s, i) => s + (i.quantity * i.unit_price), 0); },
        setPrice(index) {
            const select = document.querySelectorAll('select[name^="items"]')[index];
            const opt = select?.selectedOptions?.[0];
            if (opt) this.items[index].unit_price = parseFloat(opt.dataset.price) || 0;
        }
    }
}
</script>
@endpush
</x-layouts.app>
