<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DeliveryAgent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryAgentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DeliveryAgent::withCount(['activeDeliveries']);

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        if ($zone = $request->get('zone')) {
            $query->where('zone', $zone);
        }

        $agents = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json($agents);
    }

    public function show(DeliveryAgent $deliveryAgent): JsonResponse
    {
        $deliveryAgent->loadCount('activeDeliveries');
        $deliveryAgent->load(['deliveryAssignments' => function ($q) {
            $q->with(['order:id,order_number,client_id,total_amount', 'order.client:id,business_name,city'])
              ->latest()
              ->limit(10);
        }]);

        return response()->json(['agent' => $deliveryAgent]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'required|string|max:15|unique:delivery_agents,phone',
            'alt_phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:30',
            'license_expiry' => 'nullable|string|max:10',
            'zone' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:15',
            'id_proof_type' => 'nullable|string|max:50',
            'id_proof_number' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'is_available' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $agent = DeliveryAgent::create($validated);

        ActivityLog::log('delivery_agent.created', 'delivery_agent', $agent->id, null, $validated);

        return response()->json(['agent' => $agent], 201);
    }

    public function update(Request $request, DeliveryAgent $deliveryAgent): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:150',
            'phone' => 'string|max:15|unique:delivery_agents,phone,' . $deliveryAgent->id,
            'alt_phone' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:255',
            'vehicle_type' => 'nullable|string|max:50',
            'vehicle_number' => 'nullable|string|max:20',
            'license_number' => 'nullable|string|max:30',
            'license_expiry' => 'nullable|string|max:10',
            'zone' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:15',
            'id_proof_type' => 'nullable|string|max:50',
            'id_proof_number' => 'nullable|string|max:50',
            'joining_date' => 'nullable|date',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $oldValues = $deliveryAgent->only(array_keys($validated));
        $deliveryAgent->update($validated);

        ActivityLog::log('delivery_agent.updated', 'delivery_agent', $deliveryAgent->id, $oldValues, $validated);

        return response()->json(['agent' => $deliveryAgent->fresh()]);
    }

    public function destroy(DeliveryAgent $deliveryAgent): JsonResponse
    {
        if ($deliveryAgent->activeDeliveries()->count() > 0) {
            return response()->json(['message' => 'Cannot delete agent with active deliveries'], 422);
        }

        $deliveryAgent->delete();

        ActivityLog::log('delivery_agent.deleted', 'delivery_agent', $deliveryAgent->id);

        return response()->json(['message' => 'Delivery agent deleted']);
    }

    public function toggleAvailable(DeliveryAgent $deliveryAgent): JsonResponse
    {
        $deliveryAgent->update(['is_available' => !$deliveryAgent->is_available]);

        return response()->json(['agent' => $deliveryAgent]);
    }
}
