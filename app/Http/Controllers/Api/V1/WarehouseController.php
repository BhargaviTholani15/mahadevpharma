<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::withCount('stocks');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $warehouses = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json($warehouses);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        $warehouse->loadCount('stocks');
        $warehouse->load(['stocks' => fn($q) => $q->with('batch.product:id,name,sku')]);

        $stockSummary = [
            'total_batches' => $warehouse->stocks->count(),
            'total_quantity' => $warehouse->stocks->sum('quantity'),
            'total_reserved' => $warehouse->stocks->sum('reserved_qty'),
            'low_stock_count' => $warehouse->stocks->filter(fn($s) => $s->isLowStock())->count(),
        ];

        return response()->json([
            'warehouse' => $warehouse->makeHidden('stocks'),
            'stock_summary' => $stockSummary,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:warehouses,code',
            'state_code' => 'required|string|size:2',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|size:6',
            'is_active' => 'boolean',
        ]);

        $warehouse = Warehouse::create($validated);
        ActivityLog::log('warehouse.created', 'warehouse', $warehouse->id, null, $validated);

        return response()->json(['warehouse' => $warehouse], 201);
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:150',
            'code' => 'string|max:20|unique:warehouses,code,' . $warehouse->id,
            'state_code' => 'string|size:2',
            'address_line1' => 'string|max:255',
            'city' => 'string|max:100',
            'state' => 'string|max:100',
            'pincode' => 'string|size:6',
            'is_active' => 'boolean',
        ]);

        $oldValues = $warehouse->only(array_keys($validated));
        $warehouse->update($validated);
        ActivityLog::log('warehouse.updated', 'warehouse', $warehouse->id, $oldValues, $validated);

        return response()->json(['warehouse' => $warehouse->fresh()]);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
            return response()->json(['message' => 'Cannot delete warehouse with existing stock'], 422);
        }

        $warehouse->delete();
        ActivityLog::log('warehouse.deleted', 'warehouse', $warehouse->id);

        return response()->json(['message' => 'Warehouse deleted']);
    }

    public function stockSummary(Warehouse $warehouse): JsonResponse
    {
        $stocks = $warehouse->stocks()
            ->with(['batch.product:id,name,sku,generic_name'])
            ->where('quantity', '>', 0)
            ->orderBy('id')
            ->get()
            ->groupBy('batch.product.id')
            ->map(function ($stockGroup) {
                $product = $stockGroup->first()->batch->product;
                return [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'total_quantity' => $stockGroup->sum('quantity'),
                    'total_reserved' => $stockGroup->sum('reserved_qty'),
                    'available' => $stockGroup->sum('quantity') - $stockGroup->sum('reserved_qty'),
                    'batch_count' => $stockGroup->count(),
                ];
            })
            ->values();

        return response()->json(['warehouse' => $warehouse->only('id', 'name', 'code'), 'stocks' => $stocks]);
    }
}
