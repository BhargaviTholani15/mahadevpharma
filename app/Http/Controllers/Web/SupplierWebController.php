<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($search = $request->get('search')) {
            $query->search($search);
        }

        if ($request->has('is_active') && $request->get('is_active') !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $suppliers = $query->orderBy('name')->paginate(25)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
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

        $supplier = Supplier::create($v);
        ActivityLog::log('supplier.created', 'supplier', $supplier->id, null, $v);

        return redirect()->route('suppliers.index')->with('success', 'Supplier created.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $v = $request->validate([
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

        $old = $supplier->only(array_keys($v));
        $supplier->update($v);
        ActivityLog::log('supplier.updated', 'supplier', $supplier->id, $old, $v);

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        ActivityLog::log('supplier.deleted', 'supplier', $supplier->id);

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }
}
