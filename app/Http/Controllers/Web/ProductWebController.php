<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DosageForm;
use App\Models\DrugSchedule;
use App\Models\HsnCode;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Strength;
use App\Models\StorageCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['brand:id,name', 'category:id,name', 'hsnCode:id,code']);

        if ($search = $request->get('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('generic_name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"));
        }
        if ($brandId = $request->get('brand_id')) $query->where('brand_id', $brandId);
        if ($categoryId = $request->get('category_id')) $query->where('category_id', $categoryId);

        $products = $query->orderBy('name')->paginate(25)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'brands', 'categories'));
    }

    public function create()
    {
        return view('products.form', [
            'product' => null,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'hsnCodes' => HsnCode::orderBy('code')->get(),
            'dosageForms' => DosageForm::orderBy('sort_order')->get(),
            'strengths' => Strength::orderBy('sort_order')->get(),
            'packSizes' => PackSize::orderBy('sort_order')->get(),
            'drugSchedules' => DrugSchedule::orderBy('sort_order')->get(),
            'storageConditions' => StorageCondition::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->productRules());

        $validated['is_prescription_only'] = $request->boolean('is_prescription_only');
        $validated['is_controlled'] = $request->boolean('is_controlled');
        $validated['is_returnable'] = $request->boolean('is_returnable');

        $product = Product::create($validated);
        ActivityLog::log('product.created', 'product', $product->id, null, $validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load(['brand', 'category', 'hsnCode', 'activeBatches.warehouseStocks.warehouse']);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        return view('products.form', [
            'product' => $product,
            'brands' => Brand::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'hsnCodes' => HsnCode::orderBy('code')->get(),
            'dosageForms' => DosageForm::orderBy('sort_order')->get(),
            'strengths' => Strength::orderBy('sort_order')->get(),
            'packSizes' => PackSize::orderBy('sort_order')->get(),
            'drugSchedules' => DrugSchedule::orderBy('sort_order')->get(),
            'storageConditions' => StorageCondition::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate($this->productRules($product->id));

        $validated['is_prescription_only'] = $request->boolean('is_prescription_only');
        $validated['is_controlled'] = $request->boolean('is_controlled');
        $validated['is_returnable'] = $request->boolean('is_returnable');

        $old = $product->only(array_keys($validated));
        $product->update($validated);
        ActivityLog::log('product.updated', 'product', $product->id, $old, $validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    private function productRules(?int $productId = null): array
    {
        return [
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'hsn_code_id' => 'required|exists:hsn_codes,id',
            'sku' => 'required|string|max:50|unique:products,sku' . ($productId ? ",$productId" : ''),
            'barcode' => 'nullable|string|max:50',
            'dosage_form' => 'nullable|string|max:50',
            'route_of_administration' => 'nullable|string|max:50',
            'strength' => 'nullable|string|max:50',
            'pack_size' => 'nullable|string|max:50',
            'composition' => 'nullable|string|max:5000',
            'schedule_type' => 'nullable|string|max:50',
            'storage_conditions' => 'nullable|string|max:100',
            'shelf_life_months' => 'nullable|integer|min:0',
            'is_prescription_only' => 'boolean',
            'is_controlled' => 'boolean',
            'is_returnable' => 'boolean',
            'manufacturer_name' => 'nullable|string|max:255',
            'manufacturer_address' => 'nullable|string|max:5000',
            'country_of_origin' => 'nullable|string|max:100',
            'marketing_authorization' => 'nullable|string|max:100',
            'mrp' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'ptr' => 'nullable|numeric|min:0',
            'pts' => 'nullable|numeric|min:0',
            'margin_pct' => 'nullable|numeric|min:0|max:100',
            'min_stock_level' => 'nullable|integer|min:0',
            'reorder_level' => 'nullable|integer|min:0',
            'reorder_quantity' => 'nullable|integer|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'rack_location' => 'nullable|string|max:50',
            'near_expiry_alert_days' => 'nullable|integer|min:0',
            'batch_prefix' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:5000',
            'usage_instructions' => 'nullable|string|max:5000',
            'side_effects' => 'nullable|string|max:5000',
            'image_url' => 'nullable|url|max:500',
        ];
    }

    public function destroy(Product $product)
    {
        $product->delete();
        ActivityLog::log('product.deleted', 'product', $product->id);
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
