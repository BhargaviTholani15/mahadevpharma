<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Batch;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with([
            'supplier:id,name,contact_person',
            'warehouse:id,name,code',
            'createdBy:id,full_name',
        ]);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($supplierId = $request->get('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        if ($warehouseId = $request->get('warehouse_id')) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($orders);
    }

    public function show(PurchaseOrder $po): JsonResponse
    {
        $po->load([
            'supplier', 'warehouse',
            'createdBy:id,full_name', 'approvedBy:id,full_name',
            'items.product:id,name,sku',
        ]);

        return response()->json(['purchase_order' => $po]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.mrp' => 'required|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.mfg_date' => 'nullable|date',
            'items.*.batch_number' => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $sequence = PurchaseOrder::withTrashed()->count() + 1;
            $poNumber = 'PO-' . now()->format('Ym') . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $subtotal = 0;
            $taxTotal = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity_ordered'] * $item['unit_price'];
                $tax = $item['tax_amount'] ?? 0;
                $subtotal += $lineTotal;
                $taxTotal += $tax;
                $itemsData[] = array_merge($item, [
                    'line_total' => $lineTotal + $tax,
                    'tax_amount' => $tax,
                ]);
            }

            $po = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $validated['supplier_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'status' => 'DRAFT',
                'subtotal' => $subtotal,
                'tax_amount' => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
                'expected_delivery_date' => $validated['expected_delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($itemsData as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $item['product_id'],
                    'batch_number' => $item['batch_number'] ?? null,
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price' => $item['unit_price'],
                    'tax_amount' => $item['tax_amount'],
                    'line_total' => $item['line_total'],
                    'mrp' => $item['mrp'],
                    'mfg_date' => $item['mfg_date'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                ]);
            }

            ActivityLog::log('purchase_order.created', 'purchase_order', $po->id);

            return response()->json(['purchase_order' => $po->load('items')], 201);
        });
    }

    public function update(Request $request, PurchaseOrder $po): JsonResponse
    {
        if ($po->status !== 'DRAFT') {
            return response()->json(['message' => 'Only DRAFT purchase orders can be updated'], 422);
        }

        $validated = $request->validate([
            'supplier_id' => 'exists:suppliers,id',
            'warehouse_id' => 'exists:warehouses,id',
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'items' => 'nullable|array|min:1',
            'items.*.product_id' => 'required_with:items|exists:products,id',
            'items.*.quantity_ordered' => 'required_with:items|integer|min:1',
            'items.*.unit_price' => 'required_with:items|numeric|min:0',
            'items.*.tax_amount' => 'nullable|numeric|min:0',
            'items.*.mrp' => 'required_with:items|numeric|min:0',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.mfg_date' => 'nullable|date',
            'items.*.batch_number' => 'nullable|string|max:50',
        ]);

        return DB::transaction(function () use ($validated, $po) {
            $poUpdates = collect($validated)->only([
                'supplier_id', 'warehouse_id', 'expected_delivery_date', 'notes',
            ])->filter()->toArray();

            if (isset($validated['items'])) {
                // Replace items
                $po->items()->delete();

                $subtotal = 0;
                $taxTotal = 0;

                foreach ($validated['items'] as $item) {
                    $lineTotal = $item['quantity_ordered'] * $item['unit_price'];
                    $tax = $item['tax_amount'] ?? 0;
                    $subtotal += $lineTotal;
                    $taxTotal += $tax;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_id' => $item['product_id'],
                        'batch_number' => $item['batch_number'] ?? null,
                        'quantity_ordered' => $item['quantity_ordered'],
                        'quantity_received' => 0,
                        'unit_price' => $item['unit_price'],
                        'tax_amount' => $tax,
                        'line_total' => $lineTotal + $tax,
                        'mrp' => $item['mrp'],
                        'mfg_date' => $item['mfg_date'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                    ]);
                }

                $poUpdates['subtotal'] = $subtotal;
                $poUpdates['tax_amount'] = $taxTotal;
                $poUpdates['total_amount'] = $subtotal + $taxTotal;
            }

            $po->update($poUpdates);

            ActivityLog::log('purchase_order.updated', 'purchase_order', $po->id);

            return response()->json(['purchase_order' => $po->fresh()->load('items')]);
        });
    }

    public function approve(Request $request, PurchaseOrder $po): JsonResponse
    {
        if ($po->status !== 'DRAFT') {
            return response()->json(['message' => 'Only DRAFT purchase orders can be approved'], 422);
        }

        $po->update([
            'status' => 'SENT',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLog::log('purchase_order.approved', 'purchase_order', $po->id);

        return response()->json(['purchase_order' => $po->fresh(), 'message' => 'Purchase order approved and sent']);
    }

    public function receive(Request $request, PurchaseOrder $po): JsonResponse
    {
        if (!in_array($po->status, ['SENT', 'PARTIALLY_RECEIVED'])) {
            return response()->json(['message' => 'Only SENT or PARTIALLY_RECEIVED purchase orders can receive goods'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        return DB::transaction(function () use ($validated, $po, $request) {
            $allFullyReceived = true;

            foreach ($validated['items'] as $receivedItem) {
                if ($receivedItem['quantity_received'] <= 0) {
                    continue;
                }

                $poItem = PurchaseOrderItem::where('id', $receivedItem['purchase_order_item_id'])
                    ->where('purchase_order_id', $po->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $newReceived = $poItem->quantity_received + $receivedItem['quantity_received'];
                if ($newReceived > $poItem->quantity_ordered) {
                    throw new \Exception("Received quantity exceeds ordered quantity for item ID {$poItem->id}");
                }

                $poItem->update(['quantity_received' => $newReceived]);

                // Create batch record
                $batch = Batch::create([
                    'product_id' => $poItem->product_id,
                    'batch_number' => $poItem->batch_number ?? ('PO-' . $po->id . '-' . $poItem->id),
                    'mfg_date' => $poItem->mfg_date,
                    'expiry_date' => $poItem->expiry_date,
                    'mrp' => $poItem->mrp,
                    'purchase_price' => $poItem->unit_price,
                    'selling_price' => $poItem->mrp, // default to MRP, can be adjusted
                ]);

                // Create warehouse stock entry
                $stock = WarehouseStock::create([
                    'warehouse_id' => $po->warehouse_id,
                    'batch_id' => $batch->id,
                    'quantity' => $receivedItem['quantity_received'],
                    'reserved_qty' => 0,
                ]);

                // Create stock movement
                StockMovement::create([
                    'warehouse_id' => $po->warehouse_id,
                    'batch_id' => $batch->id,
                    'movement_type' => 'PURCHASE_IN',
                    'quantity_change' => $receivedItem['quantity_received'],
                    'quantity_before' => 0,
                    'quantity_after' => $receivedItem['quantity_received'],
                    'reference_type' => 'purchase_order',
                    'reference_id' => $po->id,
                    'reason' => "PO {$po->po_number} goods received",
                    'performed_by' => $request->user()->id,
                ]);
            }

            // Determine if all items fully received
            $po->refresh();
            foreach ($po->items as $item) {
                if ($item->quantity_received < $item->quantity_ordered) {
                    $allFullyReceived = false;
                    break;
                }
            }

            $po->update([
                'status' => $allFullyReceived ? 'RECEIVED' : 'PARTIALLY_RECEIVED',
            ]);

            ActivityLog::log('purchase_order.received', 'purchase_order', $po->id);

            return response()->json([
                'purchase_order' => $po->fresh()->load('items'),
                'message' => $allFullyReceived ? 'All items received' : 'Partial receipt recorded',
            ]);
        });
    }

    public function cancel(Request $request, PurchaseOrder $po): JsonResponse
    {
        if (!in_array($po->status, ['DRAFT', 'SENT'])) {
            return response()->json(['message' => 'Only DRAFT or SENT purchase orders can be cancelled'], 422);
        }

        $po->update(['status' => 'CANCELLED']);

        ActivityLog::log('purchase_order.cancelled', 'purchase_order', $po->id);

        return response()->json(['purchase_order' => $po->fresh(), 'message' => 'Purchase order cancelled']);
    }
}
