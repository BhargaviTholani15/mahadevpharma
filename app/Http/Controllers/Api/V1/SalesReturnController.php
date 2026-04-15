<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\LedgerEntry;
use App\Models\SalesReturn;
use App\Models\SalesReturnItem;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesReturnController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SalesReturn::with([
            'order:id,order_number',
            'client:id,business_name',
            'warehouse:id,name,code',
            'createdBy:id,full_name',
        ]);

        $user = $request->user();
        if ($user->isClient()) {
            $query->where('client_id', $user->client->id);
        } elseif ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($from = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->where('return_number', 'like', "%{$search}%");
        }

        $returns = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($returns);
    }

    public function show(SalesReturn $return): JsonResponse
    {
        $return->load([
            'order', 'client', 'warehouse',
            'createdBy:id,full_name', 'approvedBy:id,full_name',
            'items.product:id,name,sku',
            'items.batch:id,batch_number,expiry_date',
        ]);

        return response()->json(['sales_return' => $return]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'return_number' => 'nullable|string|unique:sales_returns,return_number',
            'order_id' => 'required|exists:orders,id',
            'client_id' => 'required|exists:clients,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'reason' => 'required|in:DAMAGED,EXPIRED,WRONG_PRODUCT,QUALITY_ISSUE,EXCESS_SUPPLY,OTHER',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.condition' => 'nullable|string|max:100',
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $returnNumber = $validated['return_number']
                ?? 'RET-' . str_pad(SalesReturn::withTrashed()->count() + 1, 6, '0', STR_PAD_LEFT);

            $totalAmount = 0;
            $itemsData = [];

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity'] * $item['unit_price'];
                $totalAmount += $lineTotal;
                $itemsData[] = array_merge($item, ['line_total' => $lineTotal]);
            }

            $salesReturn = SalesReturn::create([
                'return_number' => $returnNumber,
                'order_id' => $validated['order_id'],
                'client_id' => $validated['client_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'status' => 'REQUESTED',
                'reason' => $validated['reason'],
                'total_amount' => $totalAmount,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);

            foreach ($itemsData as $item) {
                SalesReturnItem::create([
                    'sales_return_id' => $salesReturn->id,
                    'product_id' => $item['product_id'],
                    'batch_id' => $item['batch_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'condition' => $item['condition'] ?? null,
                ]);
            }

            ActivityLog::log('sales_return.created', 'sales_return', $salesReturn->id);

            return response()->json([
                'sales_return' => $salesReturn->load('items'),
            ], 201);
        });
    }

    public function approve(Request $request, SalesReturn $return): JsonResponse
    {
        if ($return->status !== 'REQUESTED') {
            return response()->json(['message' => 'Only returns with REQUESTED status can be approved'], 422);
        }

        $return->update([
            'status' => 'APPROVED',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        ActivityLog::log('sales_return.approved', 'sales_return', $return->id);

        return response()->json(['sales_return' => $return->fresh(), 'message' => 'Sales return approved']);
    }

    public function receive(Request $request, SalesReturn $return): JsonResponse
    {
        if ($return->status !== 'APPROVED') {
            return response()->json(['message' => 'Only APPROVED returns can be received'], 422);
        }

        return DB::transaction(function () use ($return, $request) {
            $return->update([
                'status' => 'RECEIVED',
                'received_at' => now(),
            ]);

            foreach ($return->items as $item) {
                $stock = WarehouseStock::where('warehouse_id', $return->warehouse_id)
                    ->where('batch_id', $item->batch_id)
                    ->lockForUpdate()
                    ->first();

                $quantityBefore = $stock ? $stock->quantity : 0;
                $quantityAfter = $quantityBefore + $item->quantity;

                if ($stock) {
                    $stock->update(['quantity' => $quantityAfter]);
                } else {
                    WarehouseStock::create([
                        'warehouse_id' => $return->warehouse_id,
                        'batch_id' => $item->batch_id,
                        'quantity' => $item->quantity,
                        'reserved_qty' => 0,
                    ]);
                }

                StockMovement::create([
                    'warehouse_id' => $return->warehouse_id,
                    'batch_id' => $item->batch_id,
                    'movement_type' => 'RETURN_IN',
                    'quantity_change' => $item->quantity,
                    'quantity_before' => $quantityBefore,
                    'quantity_after' => $quantityAfter,
                    'reference_type' => 'sales_return',
                    'reference_id' => $return->id,
                    'reason' => "Sales return {$return->return_number} received",
                    'performed_by' => $request->user()->id,
                ]);
            }

            ActivityLog::log('sales_return.received', 'sales_return', $return->id);

            return response()->json(['sales_return' => $return->fresh(), 'message' => 'Sales return received and stock updated']);
        });
    }

    public function reject(Request $request, SalesReturn $return): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);

        if (!in_array($return->status, ['REQUESTED', 'APPROVED'])) {
            return response()->json(['message' => 'Only REQUESTED or APPROVED returns can be rejected'], 422);
        }

        $return->update([
            'status' => 'REJECTED',
            'notes' => $return->notes . "\nRejection reason: " . $request->reason,
        ]);

        ActivityLog::log('sales_return.rejected', 'sales_return', $return->id);

        return response()->json(['sales_return' => $return->fresh(), 'message' => 'Sales return rejected']);
    }

    public function issueCreditNote(Request $request, SalesReturn $return): JsonResponse
    {
        if ($return->status !== 'RECEIVED') {
            return response()->json(['message' => 'Credit note can only be issued for RECEIVED returns'], 422);
        }

        return DB::transaction(function () use ($return, $request) {
            $return->update(['status' => 'CREDIT_ISSUED']);

            $client = Client::lockForUpdate()->findOrFail($return->client_id);
            $lastEntry = LedgerEntry::where('client_id', $client->id)->latest('id')->first();
            $runningBalance = ($lastEntry?->running_balance ?? 0) - $return->total_amount;

            LedgerEntry::create([
                'client_id' => $client->id,
                'entry_date' => now()->toDateString(),
                'entry_type' => 'CREDIT_NOTE',
                'debit_amount' => 0,
                'credit_amount' => $return->total_amount,
                'running_balance' => $runningBalance,
                'reference_type' => 'sales_return',
                'reference_id' => $return->id,
                'narration' => "Credit note for return {$return->return_number}",
                'financial_year' => LedgerEntry::currentFinancialYear(),
                'created_by' => $request->user()->id,
            ]);

            $client->update(['current_outstanding' => max(0, $runningBalance)]);

            ActivityLog::log('sales_return.credit_issued', 'sales_return', $return->id);

            return response()->json(['sales_return' => $return->fresh(), 'message' => 'Credit note issued']);
        });
    }
}
