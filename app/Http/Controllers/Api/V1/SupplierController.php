<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Supplier::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $suppliers = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json($suppliers);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier->load([
            'purchaseOrders' => fn($q) => $q->latest()->limit(10),
            'purchaseOrders.warehouse:id,name,code',
        ]);

        return response()->json(['supplier' => $supplier]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:15',
            'gst_number' => 'nullable|string|max:15',
            'drug_license_no' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|size:2',
            'pincode' => 'nullable|string|max:10',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $supplier = Supplier::create($validated);

        ActivityLog::log('supplier.created', 'supplier', $supplier->id);

        return response()->json(['supplier' => $supplier], 201);
    }

    public function update(Request $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:255',
            'contact_person' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:255',
            'phone' => 'string|max:15',
            'gst_number' => 'nullable|string|max:15',
            'drug_license_no' => 'nullable|string|max:50',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'state_code' => 'nullable|string|size:2',
            'pincode' => 'nullable|string|max:10',
            'payment_terms_days' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $oldValues = $supplier->only(array_keys($validated));
        $supplier->update($validated);

        ActivityLog::log('supplier.updated', 'supplier', $supplier->id, $oldValues, $validated);

        return response()->json(['supplier' => $supplier->fresh()]);
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $supplier->delete();

        ActivityLog::log('supplier.deleted', 'supplier', $supplier->id);

        return response()->json(['message' => 'Supplier deleted']);
    }
}
