<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DosageForm;
use App\Models\DrugSchedule;
use App\Models\HsnCode;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Strength;
use App\Models\StorageCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LookupWebController extends Controller
{
    // ─── Categories ───────────────────────────────────────────────
    public function categories(Request $request)
    {
        $query = Category::withCount('products');
        if ($search = $request->get('search')) $query->where('name', 'like', "%{$search}%");
        $items = $query->orderBy('sort_order')->orderBy('name')->paginate(50)->withQueryString();
        return view('lookup.categories', compact('items'));
    }

    public function storeCategory(Request $request)
    {
        $v = $request->validate(['name' => 'required|string|max:100', 'sort_order' => 'integer|min:0', 'is_active' => 'boolean']);
        $v['slug'] = Str::slug($v['name']);
        $v['is_active'] = $request->boolean('is_active', true);
        Category::create($v);
        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $v = $request->validate(['name' => 'required|string|max:100', 'sort_order' => 'integer|min:0', 'is_active' => 'boolean']);
        $v['slug'] = Str::slug($v['name']);
        $v['is_active'] = $request->boolean('is_active', true);
        $category->update($v);
        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        if (Product::where('category_id', $category->id)->count() > 0)
            return back()->with('error', 'Cannot delete — products use this category.');
        $category->delete();
        return back()->with('success', 'Category deleted.');
    }

    // ─── Brands ───────────────────────────────────────────────────
    public function brands(Request $request)
    {
        $query = Brand::withCount('products');
        if ($search = $request->get('search')) $query->where('name', 'like', "%{$search}%");
        $items = $query->orderBy('name')->paginate(50)->withQueryString();
        return view('lookup.brands', compact('items'));
    }

    public function storeBrand(Request $request)
    {
        $v = $request->validate(['name' => 'required|string|max:150', 'manufacturer' => 'nullable|string|max:255']);
        $v['slug'] = Str::slug($v['name']);
        Brand::create($v);
        return back()->with('success', 'Brand created.');
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        $v = $request->validate(['name' => 'required|string|max:150', 'manufacturer' => 'nullable|string|max:255']);
        $v['slug'] = Str::slug($v['name']);
        $brand->update($v);
        return back()->with('success', 'Brand updated.');
    }

    public function destroyBrand(Brand $brand)
    {
        if (Product::where('brand_id', $brand->id)->count() > 0)
            return back()->with('error', 'Cannot delete — products use this brand.');
        $brand->delete();
        return back()->with('success', 'Brand deleted.');
    }

    // ─── HSN Codes ────────────────────────────────────────────────
    public function hsnCodes(Request $request)
    {
        $query = HsnCode::query();
        if ($search = $request->get('search')) $query->where('code', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
        $items = $query->orderBy('code')->paginate(50)->withQueryString();
        return view('lookup.hsn-codes', compact('items'));
    }

    public function storeHsnCode(Request $request)
    {
        $v = $request->validate([
            'code' => 'required|string|max:10', 'description' => 'required|string|max:255',
            'cgst_rate' => 'required|numeric|min:0|max:50', 'sgst_rate' => 'required|numeric|min:0|max:50',
            'igst_rate' => 'required|numeric|min:0|max:50', 'effective_from' => 'required|date',
        ]);
        HsnCode::create($v);
        return back()->with('success', 'HSN Code created.');
    }

    public function updateHsnCode(Request $request, HsnCode $hsnCode)
    {
        $v = $request->validate([
            'code' => 'required|string|max:10', 'description' => 'required|string|max:255',
            'cgst_rate' => 'required|numeric|min:0|max:50', 'sgst_rate' => 'required|numeric|min:0|max:50',
            'igst_rate' => 'required|numeric|min:0|max:50', 'effective_from' => 'required|date',
        ]);
        $hsnCode->update($v);
        return back()->with('success', 'HSN Code updated.');
    }

    public function destroyHsnCode(HsnCode $hsnCode)
    {
        if (Product::where('hsn_code_id', $hsnCode->id)->count() > 0)
            return back()->with('error', 'Cannot delete — products use this HSN code.');
        $hsnCode->delete();
        return back()->with('success', 'HSN Code deleted.');
    }

    // ─── Generic Lookups (Dosage Forms, Strengths, Pack Sizes, Drug Schedules, Storage Conditions) ─
    private function resolveModel(string $type): array
    {
        return match($type) {
            'dosage-forms' => [DosageForm::class, 'dosage_form', 'Dosage Form'],
            'strengths' => [Strength::class, 'strength', 'Strength'],
            'pack-sizes' => [PackSize::class, 'pack_size', 'Pack Size'],
            'drug-schedules' => [DrugSchedule::class, 'schedule_type', 'Drug Schedule'],
            'storage-conditions' => [StorageCondition::class, 'storage_conditions', 'Storage Condition'],
            default => abort(404),
        };
    }

    public function genericLookup(Request $request, string $type)
    {
        [$modelClass, $productCol, $label] = $this->resolveModel($type);
        $query = $modelClass::query();
        if ($search = $request->get('search')) $query->where('name', 'like', "%{$search}%");
        $items = $query->orderBy('sort_order')->orderBy('name')->paginate(50)->withQueryString();
        $hasDescription = $type === 'drug-schedules';
        return view('lookup.generic', compact('items', 'type', 'label', 'hasDescription'));
    }

    public function storeGenericLookup(Request $request, string $type)
    {
        [$modelClass, , $label] = $this->resolveModel($type);
        $rules = ['name' => 'required|string|max:100', 'sort_order' => 'integer|min:0'];
        if ($type === 'drug-schedules') $rules['description'] = 'nullable|string|max:255';
        $v = $request->validate($rules);
        $v['slug'] = Str::slug($v['name']);
        $modelClass::create($v);
        return back()->with('success', "{$label} created.");
    }

    public function updateGenericLookup(Request $request, string $type, int $id)
    {
        [$modelClass, , $label] = $this->resolveModel($type);
        $item = $modelClass::findOrFail($id);
        $rules = ['name' => 'required|string|max:100', 'sort_order' => 'integer|min:0'];
        if ($type === 'drug-schedules') $rules['description'] = 'nullable|string|max:255';
        $v = $request->validate($rules);
        $v['slug'] = Str::slug($v['name']);
        $item->update($v);
        return back()->with('success', "{$label} updated.");
    }

    public function destroyGenericLookup(string $type, int $id)
    {
        [$modelClass, $productCol, $label] = $this->resolveModel($type);
        $item = $modelClass::findOrFail($id);
        if (Product::where($productCol, $item->name)->count() > 0)
            return back()->with('error', "Cannot delete — products use this {$label}.");
        $item->delete();
        return back()->with('success', "{$label} deleted.");
    }
}
