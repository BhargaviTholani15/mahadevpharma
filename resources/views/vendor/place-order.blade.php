<x-layouts.app title="Place Order">
@php
    $categories = \App\Models\Category::orderBy('sort_order')->get();
    $brands = \App\Models\Brand::orderBy('name')->get();
    $warehouses = \App\Models\Warehouse::first();
@endphp

<div x-data="placeOrder()" class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">Place Order</h1><p class="text-gray-500 mt-1">Browse products, add to cart, and place your order</p></div>
        <button @click="showCart = true" class="btn-primary relative gap-2">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.66-7.88H5.12"/></svg>
            Cart
            <span x-show="cartCount > 0" x-text="cartCount" class="absolute -top-2 -right-2 w-5 h-5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"></span>
        </button>
    </div>

    {{-- Search & Filters --}}
    <div class="card">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[250px]">
                <input type="text" name="search" value="{{ request('search') }}" class="form-input" placeholder="Search medicines by name, brand, or SKU...">
            </div>
            <select name="category_id" class="form-select w-48" onchange="this.form.submit()">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <select name="brand_id" class="form-select w-48" onchange="this.form.submit()">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-primary">Search</button>
            @if(request()->hasAny(['search','category_id','brand_id']))
            <a href="{{ route('vendor.place-order') }}" class="btn-secondary">Clear</a>
            @endif
        </form>
    </div>

    {{-- Products Grid --}}
    @php
        $query = \App\Models\Product::with(['brand:id,name', 'category:id,name', 'activeBatches.warehouseStocks'])
            ->active()->orderBy('name');
        if (request('search')) $query->search(request('search'));
        if (request('category_id')) $query->where('category_id', request('category_id'));
        if (request('brand_id')) $query->where('brand_id', request('brand_id'));
        $products = $query->paginate(20)->withQueryString();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($products as $product)
        @php
            $batch = $product->activeBatches->first();
            $stock = $batch ? $batch->warehouseStocks->sum('quantity') : 0;
            $price = $product->selling_price ?? $product->mrp ?? 0;
            $mrp = $product->mrp ?? 0;
        @endphp
        <div class="card hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between mb-2">
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $product->name }}</h3>
                    <p class="text-xs text-gray-500 truncate">{{ $product->generic_name ?? $product->brand?->name ?? '' }}</p>
                </div>
                @if($product->schedule_type)
                <span class="text-[10px] font-medium bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded ml-2 shrink-0">{{ $product->schedule_type }}</span>
                @endif
            </div>
            <div class="flex items-center gap-2 text-xs text-gray-400 mb-3">
                @if($product->pack_size)<span>{{ $product->pack_size }}</span>@endif
                @if($product->dosage_form)<span>· {{ $product->dosage_form }}</span>@endif
                @if($product->brand)<span>· {{ $product->brand->name }}</span>@endif
            </div>
            <div class="flex items-end justify-between mb-3">
                <div>
                    <p class="text-lg font-bold text-gray-900">₹{{ number_format($price, 2) }}</p>
                    @if($mrp > $price)<p class="text-xs text-gray-400 line-through">₹{{ number_format($mrp, 2) }}</p>@endif
                </div>
                @if($stock <= 0)
                <span class="text-xs font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Out of Stock</span>
                @else
                <span class="text-xs text-gray-400">{{ $stock }} in stock</span>
                @endif
            </div>
            @if($stock > 0 && $batch)
            <div x-show="!inCart({{ $product->id }})">
                <button @click="addToCart({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $price }}, {{ $batch->id }})" class="btn-secondary w-full gap-2 text-green-700 border-green-200 hover:bg-green-50">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                    Add to Order
                </button>
            </div>
            <div x-show="inCart({{ $product->id }})" class="flex items-center justify-between bg-green-50 rounded-xl p-1">
                <button @click="updateQty({{ $product->id }}, -1)" class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-green-700 hover:bg-green-100 font-bold">−</button>
                <span class="font-bold text-green-700" x-text="getQty({{ $product->id }})"></span>
                <button @click="updateQty({{ $product->id }}, 1)" class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-green-700 hover:bg-green-100 font-bold">+</button>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full text-center py-16 text-gray-400">
            <p class="text-lg mb-2">No products found</p>
            <a href="{{ route('vendor.place-order') }}" class="btn-secondary">Clear filters</a>
        </div>
        @endforelse
    </div>

    @if($products->hasPages())
    <div class="card">{{ $products->links() }}</div>
    @endif

    {{-- Cart Slide-Over --}}
    <div x-show="showCart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/30 z-50" @click="showCart = false" style="display:none"></div>
    <div x-show="showCart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
         class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col" style="display:none">
        <div class="flex items-center justify-between p-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Your Order (<span x-text="cartCount"></span>)</h2>
            <button @click="showCart = false" class="p-2 rounded-lg hover:bg-gray-100">&times;</button>
        </div>
        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-if="cart.length === 0">
                <div class="text-center py-12 text-gray-400">Your cart is empty</div>
            </template>
            <template x-for="item in cart" :key="item.id">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-50 border border-gray-100">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate" x-text="item.name"></p>
                        <p class="text-xs text-gray-500">₹<span x-text="item.price.toFixed(2)"></span> × <span x-text="item.qty"></span> = <span class="font-semibold text-gray-900">₹<span x-text="(item.price * item.qty).toFixed(2)"></span></span></p>
                    </div>
                    <div class="flex items-center gap-1">
                        <button @click="updateQty(item.id, -1)" class="w-7 h-7 rounded-lg bg-white border flex items-center justify-center font-bold">−</button>
                        <span class="w-8 text-center text-sm font-bold" x-text="item.qty"></span>
                        <button @click="updateQty(item.id, 1)" class="w-7 h-7 rounded-lg bg-white border flex items-center justify-center font-bold">+</button>
                    </div>
                    <button @click="removeFromCart(item.id)" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50">✕</button>
                </div>
            </template>
            <div x-show="cart.length > 0" class="pt-3">
                <label class="form-label">Order Notes (optional)</label>
                <textarea x-model="notes" class="form-textarea resize-none h-16" placeholder="Any special instructions..."></textarea>
            </div>
        </div>
        <div x-show="cart.length > 0" class="border-t p-4 space-y-3">
            <div class="flex justify-between"><span class="text-gray-500">Items</span><span class="font-medium" x-text="cartCount"></span></div>
            <div class="flex justify-between"><span class="font-semibold text-gray-900">Estimated Total</span><span class="font-bold text-lg text-green-600">₹<span x-text="cartTotal.toFixed(2)"></span></span></div>
            <p class="text-[10px] text-gray-400">* GST will be calculated on approval</p>
            <form method="POST" action="{{ route('orders.store') }}" @submit="prepareOrder($event)">
                @csrf
                <input type="hidden" name="warehouse_id" value="{{ $warehouses?->id ?? 1 }}">
                <input type="hidden" name="is_credit_order" value="1">
                <input type="hidden" name="notes" x-bind:value="notes">
                <input type="hidden" name="items_json" x-bind:value="JSON.stringify(cart.map(i => ({batch_id: i.batch_id, quantity: i.qty, discount_pct: 0})))">
                <button type="submit" class="btn-primary w-full h-11" :disabled="submitting">
                    <span x-show="!submitting">Place Order</span>
                    <span x-show="submitting">Processing...</span>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function placeOrder() {
    return {
        cart: JSON.parse(localStorage.getItem('pharma_cart') || '[]'),
        showCart: false,
        notes: '',
        submitting: false,
        get cartCount() { return this.cart.reduce((s, i) => s + i.qty, 0); },
        get cartTotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        addToCart(id, name, price, batch_id) {
            const existing = this.cart.find(i => i.id === id);
            if (existing) { existing.qty++; } else { this.cart.push({ id, name, price, batch_id, qty: 1 }); }
            this.saveCart();
        },
        updateQty(id, delta) {
            const item = this.cart.find(i => i.id === id);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) this.cart = this.cart.filter(i => i.id !== id);
            this.saveCart();
        },
        removeFromCart(id) { this.cart = this.cart.filter(i => i.id !== id); this.saveCart(); },
        inCart(id) { return this.cart.some(i => i.id === id); },
        getQty(id) { return this.cart.find(i => i.id === id)?.qty || 0; },
        saveCart() { localStorage.setItem('pharma_cart', JSON.stringify(this.cart)); },
        prepareOrder(e) { this.submitting = true; localStorage.removeItem('pharma_cart'); },
    };
}
</script>
</x-layouts.app>
