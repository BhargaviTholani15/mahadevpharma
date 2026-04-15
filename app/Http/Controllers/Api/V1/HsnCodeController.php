<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\HsnCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HsnCodeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = HsnCode::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $hsnCodes = $query->orderBy('code')->get();

        return response()->json(['hsn_codes' => $hsnCodes]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10',
            'description' => 'required|string|max:255',
            'cgst_rate' => 'required|numeric|min:0|max:50',
            'sgst_rate' => 'required|numeric|min:0|max:50',
            'igst_rate' => 'required|numeric|min:0|max:50',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
        ]);

        $hsnCode = HsnCode::create($validated);
        ActivityLog::log('hsn_code.created', 'hsn_code', $hsnCode->id, null, $validated);

        return response()->json(['hsn_code' => $hsnCode], 201);
    }

    public function update(Request $request, HsnCode $hsnCode): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'string|max:10',
            'description' => 'string|max:255',
            'cgst_rate' => 'numeric|min:0|max:50',
            'sgst_rate' => 'numeric|min:0|max:50',
            'igst_rate' => 'numeric|min:0|max:50',
            'effective_from' => 'date',
            'effective_to' => 'nullable|date',
        ]);

        $oldValues = $hsnCode->only(array_keys($validated));
        $hsnCode->update($validated);
        ActivityLog::log('hsn_code.updated', 'hsn_code', $hsnCode->id, $oldValues, $validated);

        return response()->json(['hsn_code' => $hsnCode->fresh()]);
    }

    public function destroy(HsnCode $hsnCode): JsonResponse
    {
        $productCount = \App\Models\Product::where('hsn_code_id', $hsnCode->id)->count();
        if ($productCount > 0) {
            return response()->json(['message' => "Cannot delete — {$productCount} products use this HSN code"], 422);
        }

        $hsnCode->delete();
        ActivityLog::log('hsn_code.deleted', 'hsn_code', $hsnCode->id);

        return response()->json(['message' => 'HSN Code deleted']);
    }
}
