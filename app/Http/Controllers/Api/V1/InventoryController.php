<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function stocks(Request $request): JsonResponse
    {
        $query = WarehouseStock::with([
            'batch:id,product_id,batch_number,expiry_date,mrp,selling_price',
            'batch.product:id,name,sku,brand_id',
            'batch.product.brand:id,name',
            'warehouse:id,name,code',
        ]);

        if ($search = $request->get('search')) {
            $query->whereHas('batch', function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($request->boolean('low_stock')) {
            $query->lowStock();
        }

        $stocks = $query->paginate($request->get('per_page', 25));

        return response()->json($stocks);
    }

    public function batches(Request $request): JsonResponse
    {
        $query = Batch::with(['product:id,name,sku', 'warehouseStocks.warehouse:id,name,code'])
            ->active();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($productId = $request->get('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($request->boolean('expiring_soon')) {
            $days = $request->get('expiry_days', 90);
            $query->expiringSoon($days);
        }

        if ($request->boolean('expired')) {
            $query->where('expiry_date', '<=', now());
        }

        $batches = $query->orderBy('expiry_date')->paginate($request->get('per_page', 25));

        return response()->json($batches);
    }

    public function storeBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'batch_number' => 'required|string|max:50',
            'mfg_date' => 'nullable|date',
            'expiry_date' => 'required|date|after:today',
            'mrp' => 'required|numeric|min:0',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reorder_level' => 'nullable|integer|min:0',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $batch = Batch::create([
                'product_id' => $validated['product_id'],
                'batch_number' => $validated['batch_number'],
                'mfg_date' => $validated['mfg_date'] ?? null,
                'expiry_date' => $validated['expiry_date'],
                'mrp' => $validated['mrp'],
                'purchase_price' => $validated['purchase_price'],
                'selling_price' => $validated['selling_price'],
            ]);

            $stock = WarehouseStock::create([
                'warehouse_id' => $validated['warehouse_id'],
                'batch_id' => $batch->id,
                'quantity' => $validated['quantity'],
                'reserved_qty' => 0,
                'reorder_level' => $validated['reorder_level'] ?? null,
            ]);

            StockMovement::create([
                'warehouse_id' => $validated['warehouse_id'],
                'batch_id' => $batch->id,
                'movement_type' => 'PURCHASE_IN',
                'quantity_change' => $validated['quantity'],
                'quantity_before' => 0,
                'quantity_after' => $validated['quantity'],
                'reference_type' => 'batch',
                'reference_id' => $batch->id,
                'reason' => 'Initial stock entry',
                'performed_by' => $request->user()->id,
            ]);

            return response()->json([
                'batch' => $batch->load('product'),
                'stock' => $stock,
            ], 201);
        });
    }

    public function adjustStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'batch_id' => 'required|exists:batches,id',
            'adjustment' => 'required|integer',
            'reason' => 'required|string|max:255',
            'movement_type' => 'required|in:ADJUSTMENT,DAMAGED,EXPIRED,RETURN_IN,RETURN_OUT',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $stock = WarehouseStock::where('warehouse_id', $validated['warehouse_id'])
                ->where('batch_id', $validated['batch_id'])
                ->lockForUpdate()
                ->firstOrFail();

            $before = $stock->quantity;
            $after = $before + $validated['adjustment'];

            if ($after < 0) {
                return response()->json(['message' => 'Insufficient stock'], 422);
            }

            $stock->update(['quantity' => $after]);

            StockMovement::create([
                'warehouse_id' => $validated['warehouse_id'],
                'batch_id' => $validated['batch_id'],
                'movement_type' => $validated['movement_type'],
                'quantity_change' => $validated['adjustment'],
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reason' => $validated['reason'],
                'performed_by' => $request->user()->id,
            ]);

            return response()->json(['stock' => $stock->fresh(), 'message' => 'Stock adjusted']);
        });
    }

    public function movements(Request $request): JsonResponse
    {
        $query = StockMovement::with([
            'batch:id,batch_number,product_id',
            'batch.product:id,name,sku',
            'warehouse:id,name,code',
            'performedBy:id,full_name',
        ]);

        if ($search = $request->get('search')) {
            $query->whereHas('batch', function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhereHas('product', fn($pq) => $pq->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            });
        }

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($batchId = $request->get('batch_id')) {
            $query->where('batch_id', $batchId);
        }

        if ($type = $request->get('movement_type')) {
            $query->where('movement_type', $type);
        }

        $movements = $query->latest('created_at')->paginate($request->get('per_page', 25));

        return response()->json($movements);
    }

    public function warehouses(): JsonResponse
    {
        return response()->json(Warehouse::active()->get());
    }

    public function lowStockAlerts(): JsonResponse
    {
        $alerts = WarehouseStock::lowStock()
            ->with([
                'batch:id,product_id,batch_number,expiry_date',
                'batch.product:id,name,sku',
                'warehouse:id,name,code',
            ])
            ->get()
            ->map(fn($s) => [
                'product' => $s->batch->product->name,
                'sku' => $s->batch->product->sku,
                'warehouse' => $s->warehouse->name,
                'batch' => $s->batch->batch_number,
                'available' => $s->availableQty(),
                'reorder_level' => $s->reorder_level,
            ]);

        return response()->json($alerts);
    }

    public function expiryAlerts(Request $request): JsonResponse
    {
        $days = $request->get('days', 90);

        $expiring = Batch::expiringSoon($days)
            ->with(['product:id,name,sku', 'warehouseStocks:id,batch_id,warehouse_id,quantity', 'warehouseStocks.warehouse:id,name,code'])
            ->active()
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($b) => [
                'product' => $b->product->name,
                'sku' => $b->product->sku,
                'batch' => $b->batch_number,
                'expiry_date' => $b->expiry_date->format('Y-m-d'),
                'days_left' => (int) now()->diffInDays($b->expiry_date),
                'stocks' => $b->warehouseStocks->map(fn($s) => [
                    'warehouse' => $s->warehouse->name,
                    'quantity' => $s->quantity,
                ]),
            ]);

        return response()->json($expiring);
    }
}
