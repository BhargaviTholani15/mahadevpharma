<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('children');

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json(['categories' => $categories]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        if (Category::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-' . Str::random(4);
        }

        $category = Category::create($validated);
        ActivityLog::log('category.created', 'category', $category->id, null, $validated);

        return response()->json(['category' => $category], 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'string|max:100',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
            if (Category::where('slug', $validated['slug'])->where('id', '!=', $category->id)->exists()) {
                $validated['slug'] .= '-' . Str::random(4);
            }
        }

        $oldValues = $category->only(array_keys($validated));
        $category->update($validated);
        ActivityLog::log('category.updated', 'category', $category->id, $oldValues, $validated);

        return response()->json(['category' => $category->fresh()]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if ($category->children()->count() > 0) {
            return response()->json(['message' => 'Cannot delete category with sub-categories'], 422);
        }

        $productCount = \App\Models\Product::where('category_id', $category->id)->count();
        if ($productCount > 0) {
            return response()->json(['message' => "Cannot delete — {$productCount} products use this category"], 422);
        }

        $category->delete();
        ActivityLog::log('category.deleted', 'category', $category->id);

        return response()->json(['message' => 'Category deleted']);
    }
}
