<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Warehouse::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active') && $request->get('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $warehouses = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('warehouses.index', compact('warehouses'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:warehouses,code',
            'state_code' => 'required|string|size:2',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|size:6',
            'is_active' => 'boolean',
        ]);

        $warehouse = Warehouse::create($v);
        ActivityLog::log('warehouse.created', 'warehouse', $warehouse->id, null, $v);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse created.');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $v = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id,
            'state_code' => 'required|string|size:2',
            'address_line1' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|size:6',
            'is_active' => 'boolean',
        ]);

        $old = $warehouse->only(array_keys($v));
        $warehouse->update($v);
        ActivityLog::log('warehouse.updated', 'warehouse', $warehouse->id, $old, $v);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse updated.');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
            return redirect()->route('warehouses.index')->with('error', 'Cannot delete warehouse with existing stock.');
        }

        $warehouse->delete();
        ActivityLog::log('warehouse.deleted', 'warehouse', $warehouse->id);

        return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted.');
    }
}
