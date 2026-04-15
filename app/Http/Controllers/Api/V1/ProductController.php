<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HsnCode;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with([
            'brand:id,name', 'category:id,name', 'hsnCode:id,code,cgst_rate,sgst_rate,igst_rate',
            'activeBatches:id,product_id,batch_number,expiry_date',
            'activeBatches.warehouseStocks:id,batch_id,quantity,reserved_qty',
        ]);

        if (!$request->has('include_inactive')) {
            $query->active();
        }

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($brandId = $request->get('brand_id')) {
            $query->where('brand_id', $brandId);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($dosageForm = $request->get('dosage_form')) {
            $query->where('dosage_form', $dosageForm);
        }

        if ($schedule = $request->get('schedule_type')) {
            $query->where('schedule_type', $schedule);
        }

        if ($request->boolean('is_controlled')) {
            $query->controlled();
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock($request->get('stock_threshold', 50));
        }

        $sortBy = $request->get('sort_by', 'name');
        $sortDir = $request->get('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        $products = $query->paginate($request->get('per_page', 25));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        $product->load([
            'brand', 'category', 'hsnCode',
            'activeBatches' => fn($q) => $q->with(['warehouseStocks.warehouse:id,name,code']),
        ]);

        return response()->json(['product' => $product]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            // Core
            'name' => 'required|string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'hsn_code_id' => 'required|exists:hsn_codes,id',
            'dosage_form' => 'nullable|string|max:50',
            'strength' => 'nullable|string|max:50',
            'pack_size' => 'nullable|string|max:50',
            'sku' => 'required|string|max:50|unique:products,sku',

            // Pharmaceutical Details
            'composition' => 'nullable|string|max:5000',
            'schedule_type' => 'nullable|string|in:OTC,Schedule H,Schedule H1,Schedule X,Schedule G,Narcotics',
            'storage_conditions' => 'nullable|string|max:100',
            'shelf_life_months' => 'nullable|integer|min:1|max:120',
            'is_controlled' => 'boolean',
            'is_returnable' => 'boolean',

            // Manufacturer & Regulatory
            'manufacturer_name' => 'nullable|string|max:255',
            'manufacturer_address' => 'nullable|string|max:2000',
            'country_of_origin' => 'nullable|string|max:100',
            'marketing_authorization' => 'nullable|string|max:100',

            // Default Pricing
            'mrp' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',

            // Inventory Defaults
            'min_stock_level' => 'integer|min:0',
            'reorder_level' => 'integer|min:0',
            'reorder_quantity' => 'integer|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'rack_location' => 'nullable|string|max:50',

            // Description & Media
            'description' => 'nullable|string|max:10000',
            'usage_instructions' => 'nullable|string|max:5000',
            'side_effects' => 'nullable|string|max:5000',
            'image_url' => 'nullable|string|max:500',
            'barcode' => 'nullable|string|max:50|unique:products,barcode',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',

            // Batch Configuration
            'batch_prefix' => 'nullable|string|max:20',
            'near_expiry_alert_days' => 'integer|min:1|max:365',
        ]);

        $product = Product::create($validated);

        ActivityLog::log('product.created', 'product', $product->id, null, $validated);

        return response()->json(['product' => $product->load('brand', 'category', 'hsnCode')], 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'generic_name' => 'nullable|string|max:255',
            'brand_id' => 'nullable|exists:brands,id',
            'category_id' => 'nullable|exists:categories,id',
            'hsn_code_id' => 'exists:hsn_codes,id',
            'dosage_form' => 'nullable|string|max:50',
            'strength' => 'nullable|string|max:50',
            'pack_size' => 'nullable|string|max:50',
            'sku' => 'string|max:50|unique:products,sku,' . $product->id,
            'is_active' => 'boolean',

            'composition' => 'nullable|string|max:5000',
            'schedule_type' => 'nullable|string|in:OTC,Schedule H,Schedule H1,Schedule X,Schedule G,Narcotics',
            'storage_conditions' => 'nullable|string|max:100',
            'shelf_life_months' => 'nullable|integer|min:1|max:120',
            'is_controlled' => 'boolean',
            'is_returnable' => 'boolean',

            'manufacturer_name' => 'nullable|string|max:255',
            'manufacturer_address' => 'nullable|string|max:2000',
            'country_of_origin' => 'nullable|string|max:100',
            'marketing_authorization' => 'nullable|string|max:100',

            'mrp' => 'nullable|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',

            'min_stock_level' => 'integer|min:0',
            'reorder_level' => 'integer|min:0',
            'reorder_quantity' => 'integer|min:0',
            'lead_time_days' => 'nullable|integer|min:0',
            'rack_location' => 'nullable|string|max:50',

            'description' => 'nullable|string|max:10000',
            'usage_instructions' => 'nullable|string|max:5000',
            'side_effects' => 'nullable|string|max:5000',
            'image_url' => 'nullable|string|max:500',
            'barcode' => 'nullable|string|max:50|unique:products,barcode,' . $product->id,
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',

            'batch_prefix' => 'nullable|string|max:20',
            'near_expiry_alert_days' => 'integer|min:1|max:365',
        ]);

        $oldValues = $product->only(array_keys($validated));
        $product->update($validated);

        ActivityLog::log('product.updated', 'product', $product->id, $oldValues, $validated);

        return response()->json(['product' => $product->fresh()->load('brand', 'category', 'hsnCode')]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete(); // soft delete

        ActivityLog::log('product.deleted', 'product', $product->id);

        return response()->json(['message' => 'Product deleted']);
    }

    public function brands(): JsonResponse
    {
        return response()->json(\App\Models\Brand::active()->orderBy('name')->get());
    }

    public function categories(): JsonResponse
    {
        return response()->json(\App\Models\Category::active()->with('children')->roots()->orderBy('sort_order')->get());
    }

    public function lookupBarcode(Request $request): JsonResponse
    {
        $request->validate(['barcode' => 'required|string']);

        $product = Product::with([
            'brand:id,name', 'category:id,name', 'hsnCode:id,code,cgst_rate,sgst_rate,igst_rate',
            'activeBatches' => fn($q) => $q->with(['warehouseStocks.warehouse:id,name,code']),
        ])->where('barcode', $request->get('barcode'))->first();

        if (!$product) {
            return response()->json(['message' => 'Product not found for this barcode'], 404);
        }

        return response()->json(['product' => $product]);
    }

    public function hsnCodes(Request $request): JsonResponse
    {
        $query = HsnCode::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('code')->limit(50)->get());
    }
}
