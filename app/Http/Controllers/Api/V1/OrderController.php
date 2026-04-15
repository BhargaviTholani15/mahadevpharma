<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['client:id,business_name', 'warehouse:id,name,code', 'createdBy:id,full_name']);

        $user = $request->user();
        if ($user->isClient()) {
            $query->where('client_id', $user->client->id);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        if ($from = $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $to);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($cq) => $cq->where('business_name', 'like', "%{$search}%"));
            });
        }

        $orders = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        $order->load([
            'client', 'warehouse', 'createdBy:id,full_name',
            'approvedBy:id,full_name', 'items.product:id,name,sku',
            'items.batch:id,batch_number,expiry_date', 'invoice',
            'deliveryAssignment.assignedTo:id,full_name',
        ]);

        return response()->json(['order' => $order]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'is_credit_order' => 'boolean',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.batch_id' => 'required|exists:batches,id',
            'items.*.quantity' => 'required|integer|min:1|max:10000',
            'items.*.discount_pct' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        $clientId = $user->isClient() ? $user->client->id : $request->validate(['client_id' => 'required|exists:clients,id'])['client_id'];

        try {
            $order = $this->orderService->placeOrder($validated, $clientId, $user->id);
            ActivityLog::log('order.placed', 'order', $order->id);
            return response()->json(['order' => $order], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function approve(Request $request, Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->approveOrder($order, $request->user()->id);
            ActivityLog::log('order.approved', 'order', $order->id);
            return response()->json(['order' => $order, 'message' => 'Order approved and invoice generated']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, Order $order): JsonResponse
    {
        $request->validate(['reason' => 'required|string|max:255']);

        if ($order->status !== 'PENDING') {
            return response()->json(['message' => 'Only pending orders can be rejected'], 422);
        }

        $order->update([
            'status' => 'CANCELLED',
            'cancelled_by' => $request->user()->id,
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        ActivityLog::log('order.rejected', 'order', $order->id);

        return response()->json(['order' => $order, 'message' => 'Order rejected']);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:PACKED,OUT_FOR_DELIVERY,DELIVERED',
        ]);

        $validTransitions = [
            'APPROVED' => ['PACKED'],
            'PACKED' => ['OUT_FOR_DELIVERY'],
            'OUT_FOR_DELIVERY' => ['DELIVERED'],
        ];

        if (!isset($validTransitions[$order->status]) ||
            !in_array($validated['status'], $validTransitions[$order->status])) {
            return response()->json(['message' => "Cannot transition from {$order->status} to {$validated['status']}"], 422);
        }

        if ($validated['status'] === 'DELIVERED') {
            try {
                $order = $this->orderService->markDelivered($order, $request->user()->id);
            } catch (\Exception $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
        } else {
            $updates = ['status' => $validated['status']];
            if ($validated['status'] === 'PACKED') {
                $updates['packed_by'] = $request->user()->id;
                $updates['packed_at'] = now();
            }
            $order->update($updates);
        }

        ActivityLog::log("order.{$validated['status']}", 'order', $order->id);

        return response()->json(['order' => $order->fresh()]);
    }
}
