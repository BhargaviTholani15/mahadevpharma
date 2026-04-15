<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DeliveryAssignment;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DeliveryAssignment::with([
            'order:id,order_number,client_id,total_amount,status',
            'order.client:id,business_name,address_line1,city',
            'assignedTo:id,full_name,phone',
            'assignedBy:id,full_name',
            'deliveryAgent:id,name,phone,vehicle_number,zone',
        ]);

        $user = $request->user();
        if ($user->isClient()) {
            $query->whereHas('order', fn($q) => $q->where('client_id', $user->client->id));
        } elseif ($user->isStaff()) {
            $query->where('assigned_to', $user->id);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($date = $request->get('scheduled_date')) {
            $query->where('scheduled_date', $date);
        }

        $deliveries = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json($deliveries);
    }

    public function assign(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'assigned_to' => 'nullable|exists:users,id',
            'delivery_agent_id' => 'nullable|exists:delivery_agents,id',
            'scheduled_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if (empty($validated['assigned_to']) && empty($validated['delivery_agent_id'])) {
            return response()->json(['message' => 'Either a staff user or delivery agent must be assigned'], 422);
        }

        $order = Order::findOrFail($validated['order_id']);
        if (!in_array($order->status, ['APPROVED', 'PACKED'])) {
            return response()->json(['message' => 'Order must be approved or packed for delivery assignment'], 422);
        }

        $assignment = DeliveryAssignment::create([
            'order_id' => $validated['order_id'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'delivery_agent_id' => $validated['delivery_agent_id'] ?? null,
            'assigned_by' => $request->user()->id,
            'status' => 'ASSIGNED',
            'scheduled_date' => $validated['scheduled_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Generate delivery OTP
        $otp = $assignment->generateOtp();

        ActivityLog::log('delivery.assigned', 'delivery_assignment', $assignment->id);

        return response()->json([
            'assignment' => $assignment->load('assignedTo:id,full_name', 'order'),
            'otp' => app()->environment('local') ? $otp : null,
            'message' => 'Delivery assigned. OTP sent to client.',
        ], 201);
    }

    public function updateStatus(Request $request, DeliveryAssignment $assignment): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:PICKED_UP,IN_TRANSIT,DELIVERED,FAILED',
            'failure_reason' => 'required_if:status,FAILED|nullable|string|max:255',
            'delivery_otp' => 'required_if:status,DELIVERED|nullable|string|size:6',
            'delivery_lat' => 'nullable|numeric',
            'delivery_lng' => 'nullable|numeric',
        ]);

        if ($validated['status'] === 'DELIVERED') {
            if (!$assignment->verifyOtp($validated['delivery_otp'] ?? '')) {
                return response()->json(['message' => 'Invalid or expired OTP'], 422);
            }

            $assignment->update([
                'status' => 'DELIVERED',
                'delivered_at' => now(),
                'otp_verified_at' => now(),
                'delivery_lat' => $validated['delivery_lat'] ?? null,
                'delivery_lng' => $validated['delivery_lng'] ?? null,
            ]);

            // Update order status
            $assignment->order->update(['status' => 'DELIVERED']);
        } else {
            $updates = ['status' => $validated['status']];
            if ($validated['status'] === 'FAILED') {
                $updates['failure_reason'] = $validated['failure_reason'];
            }
            $assignment->update($updates);
        }

        ActivityLog::log("delivery.{$validated['status']}", 'delivery_assignment', $assignment->id);

        return response()->json(['assignment' => $assignment->fresh()]);
    }
}
