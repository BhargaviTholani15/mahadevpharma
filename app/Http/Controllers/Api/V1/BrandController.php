<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Brand::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('manufacturer', 'like', "%{$search}%");
            });
        }

        $brands = $query->orderBy('name')->get();

        return response()->json(['brands' => $brands]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'manufacturer' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (Brand::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-' . Str::random(4);
        }

        $brand = Brand::create($validated);
        ActivityLog::log('brand.created', 'brand', $brand->id, null, $validated);

        return response()->json(['brand' => $brand], 201);
    }

    public function update(Request $request, Brand $brand): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:150',
            'manufacturer' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
            if (Brand::where('slug', $validated['slug'])->where('id', '!=', $brand->id)->exists()) {
                $validated['slug'] .= '-' . Str::random(4);
            }
        }

        $oldValues = $brand->only(array_keys($validated));
        $brand->update($validated);
        ActivityLog::log('brand.updated', 'brand', $brand->id, $oldValues, $validated);

        return response()->json(['brand' => $brand->fresh()]);
    }

    public function destroy(Brand $brand): JsonResponse
    {
        $productCount = \App\Models\Product::where('brand_id', $brand->id)->count();
        if ($productCount > 0) {
            return response()->json(['message' => "Cannot delete — {$productCount} products use this brand"], 422);
        }

        $brand->delete();
        ActivityLog::log('brand.deleted', 'brand', $brand->id);

        return response()->json(['message' => 'Brand deleted']);
    }
}
