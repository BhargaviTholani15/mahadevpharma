<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::with([
            'fromWarehouse:id,name,code',
            'toWarehouse:id,name,code',
            'createdBy:id,full_name',
        ]);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($fromWarehouseId = $request->get('from_warehouse_id')) {
            $query->where('from_warehouse_id', $fromWarehouseId);
        }

        if ($toWarehouseId = $request->get('to_warehouse_id')) {
            $query->where('to_warehouse_id', $toWarehouseId);
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $transfers = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($transfers);
    }

    public function show(StockTransfer $transfer): JsonResponse
    {
        $transfer->load([
            'fromWarehouse', 'toWarehouse',
            'createdBy:id,full_name', 'approvedBy:id,full_name',
            'items.product:id,name,sku',
            'items.batch:id,batch_number,expiry_date',
        ]);

        return response()->json(['transfer' => $transfer]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Check sufficient stock for all items
            foreach ($validated['items'] as $item) {
                $stock = WarehouseStock::where('warehouse_id', $validated['from_warehouse_id'])
                    ->where('batch_id', $item['batch_id'])
                    ->first();

                if (!$stock || $stock->availableQty() < $item['quantity']) {
                    $batchId = $item['batch_id'];
                    throw new \Exception("Insufficient stock for batch ID {$batchId} in source warehouse");
                }
            }

            $sequence = StockTransfer::withTrashed()->count() + 1;
            $transferNumber = 'TRF-' . now()->format('Ymd') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'status' => 'DRAFT',
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            ActivityLog::log('stock_transfer.created', 'stock_transfer', $transfer->id);

            return response()->json(['transfer' => $transfer->load('items')], 201);
        });
    }

    public function approve(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($transfer->status !== 'DRAFT') {
            return response()->json(['message' => 'Only DRAFT transfers can be approved'], 422);
        }

        $transfer->update([
            'status' => 'APPROVED',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLog::log('stock_transfer.approved', 'stock_transfer', $transfer->id);

        return response()->json(['transfer' => $transfer->fresh(), 'message' => 'Transfer approved']);
    }

    public function ship(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($transfer->status !== 'APPROVED') {
            return response()->json(['message' => 'Only APPROVED transfers can be shipped'], 422);
        }

        return DB::transaction(function () use ($transfer, $request) {
            $transfer->update([
                'status' => 'IN_TRANSIT',
                'shipped_at' => now(),
            ]);

            foreach ($transfer->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $transfer->from_warehouse_id)
                    ->where('batch_id', $item->batch_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($stock->availableQty() < $item->quantity) {
                    throw new \Exception("Insufficient stock for batch ID {$item->batch_id}");
                }

                $quantityBefore = $stock->quantity;
                $quantityAfter = $quantityBefore - $item->quantity;
                $stock->update(['quantity' => $quantityAfter]);

                StockMovement::create([
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'batch_id' => $item->batch_id,
                    'movement_type' => 'TRANSFER_OUT',
                    'quantity_change' => -$item->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $transfer->id,
                    'reason' => "Transfer {$transfer->transfer_number} shipped",
                    'performed_by' => $request->user()->id,
                ]);
            }

            ActivityLog::log('stock_transfer.shipped', 'stock_transfer', $transfer->id);

            return response()->json(['transfer' => $transfer->fresh(), 'message' => 'Transfer shipped, stock deducted from source']);
        });
    }

    public function receive(Request $request, StockTransfer $transfer): JsonResponse
    {
        if ($transfer->status !== 'IN_TRANSIT') {
            return response()->json(['message' => 'Only IN_TRANSIT transfers can be received'], 422);
        }

        return DB::transaction(function () use ($transfer, $request) {
            $transfer->update([
                'status' => 'RECEIVED',
                'received_at' => now(),
            ]);

            foreach ($transfer->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $transfer->to_warehouse_id)
                    ->where('batch_id', $item->batch_id)
                    ->lockForUpdate()
                    ->first();

                $quantityBefore = $stock ? $stock->quantity : 0;
                $quantityAfter = $quantityBefore + $item->quantity;

                if ($stock) {
                    $stock->update(['quantity' => $quantityAfter]);
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $transfer->to_warehouse_id,
                        'batch_id' => $item->batch_id,
                        'quantity' => $item->quantity,
                        'reserved_qty' => 0,
                    ]);
                }

                StockMovement::create([
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'batch_id' => $item->batch_id,
                    'movement_type' => 'TRANSFER_IN',
                    'quantity_change' => $item->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'reference_type' => 'stock_transfer',
                    'reference_id' => $transfer->id,
                    'reason' => "Transfer {$transfer->transfer_number} received",
                    'performed_by' => $request->user()->id,
                ]);
            }

            ActivityLog::log('stock_transfer.received', 'stock_transfer', $transfer->id);

            return response()->json(['transfer' => $transfer->fresh(), 'message' => 'Transfer received, stock added to destination']);
        });
    }

    public function cancel(Request $request, StockTransfer $transfer): JsonResponse
    {
        if (!in_array($transfer->status, ['DRAFT', 'APPROVED', 'IN_TRANSIT'])) {
            return response()->json(['message' => 'This transfer cannot be cancelled'], 422);
        }

        return DB::transaction(function () use ($transfer, $request) {
            // If IN_TRANSIT, reverse the stock deduction from source warehouse
            if ($transfer->status === 'IN_TRANSIT') {
                foreach ($transfer->items as $item) {
                    $stock = WarehouseStock::where('warehouse_id', $transfer->from_warehouse_id)
                        ->where('batch_id', $item->batch_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $quantityBefore = $stock->quantity;
                    $quantityAfter = $quantityBefore + $item->quantity;
                    $stock->update(['quantity' => $quantityAfter]);

                    StockMovement::create([
                        'warehouse_id' => $transfer->from_warehouse_id,
                        'batch_id' => $item->batch_id,
                        'movement_type' => 'TRANSFER_IN',
                        'quantity_change' => $item->quantity,
                        'quantity_before' => $quantityBefore,
                        'quantity_after' => $quantityAfter,
                        'reference_type' => 'stock_transfer',
                        'reference_id' => $transfer->id,
                        'reason' => "Transfer {$transfer->transfer_number} cancelled - stock reversed",
                        'performed_by' => $request->user()->id,
                    ]);
                }
            }

            $transfer->update(['status' => 'CANCELLED']);

            ActivityLog::log('stock_transfer.cancelled', 'stock_transfer', $transfer->id);

            return response()->json(['transfer' => $transfer->fresh(), 'message' => 'Transfer cancelled']);
        });
    }
}
