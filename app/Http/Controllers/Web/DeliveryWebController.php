<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DeliveryAgent;
use App\Models\DeliveryAssignment;
use Illuminate\Http\Request;

class DeliveryWebController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryAssignment::with(['order.client:id,business_name', 'deliveryAgent:id,name,phone']);
        if (auth()->user()->isClient()) {
            $query->whereHas('order', fn($q) => $q->where('client_id', auth()->user()->client?->id));
        }
        $deliveries = $query->latest()->paginate(25)->withQueryString();
        return view('deliveries.index', compact('deliveries'));
    }

    public function agents(Request $request)
    {
        $agents = DeliveryAgent::latest()->paginate(25);
        return view('deliveries.agents', compact('agents'));
    }

    public function storeAgent(Request $request)
    {
        $v = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:15|unique:delivery_agents,phone',
            'vehicle_number' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
        ]);
        DeliveryAgent::create($v);
        return back()->with('success', 'Delivery agent added.');
    }

    public function updateAgent(Request $request, DeliveryAgent $deliveryAgent)
    {
        $v = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:15|unique:delivery_agents,phone,' . $deliveryAgent->id,
            'vehicle_number' => 'nullable|string|max:20',
            'vehicle_type' => 'nullable|string|max:50',
        ]);
        $deliveryAgent->update($v);
        return back()->with('success', 'Delivery agent updated.');
    }

    public function destroyAgent(DeliveryAgent $deliveryAgent)
    {
        $deliveryAgent->delete();
        return back()->with('success', 'Delivery agent deleted.');
    }
}
