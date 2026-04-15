<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DosageForm;
use App\Models\DrugSchedule;
use App\Models\PackSize;
use App\Models\Product;
use App\Models\Strength;
use App\Models\StorageCondition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LookupController extends Controller
{
    private array $modelMap = [
        'dosage-forms' => [DosageForm::class, 'dosage_forms', 'dosage_form', 'dosage_form'],
        'strengths' => [Strength::class, 'strengths', 'strength', 'strength'],
        'pack-sizes' => [PackSize::class, 'pack_sizes', 'pack_size', 'pack_size'],
        'drug-schedules' => [DrugSchedule::class, 'drug_schedules', 'drug_schedule', 'schedule_type'],
        'storage-conditions' => [StorageCondition::class, 'storage_conditions', 'storage_condition', 'storage_conditions'],
    ];

    private function resolve(string $type): array
    {
        return $this->modelMap[$type] ?? abort(404);
    }

    public function index(Request $request, string $type): JsonResponse
    {
        [$modelClass, $key] = $this->resolve($type);
        $query = $modelClass::query();

        if ($search = $request->get('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $items = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json([$key => $items]);
    }

    public function store(Request $request, string $type): JsonResponse
    {
        [$modelClass, $key, $singular] = $this->resolve($type);

        $rules = [
            'name' => 'required|string|max:100',
            'sort_order' => 'integer|min:0',
        ];
        if ($type === 'drug-schedules') {
            $rules['description'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);
        $validated['slug'] = Str::slug($validated['name']);

        if ($modelClass::where('slug', $validated['slug'])->exists()) {
            $validated['slug'] .= '-' . Str::random(4);
        }

        $item = $modelClass::create($validated);
        ActivityLog::log("{$singular}.created", $singular, $item->id, null, $validated);

        return response()->json([$singular => $item], 201);
    }

    public function update(Request $request, string $type, int $id): JsonResponse
    {
        [$modelClass, , $singular] = $this->resolve($type);
        $item = $modelClass::findOrFail($id);

        $rules = [
            'name' => 'string|max:100',
            'sort_order' => 'integer|min:0',
        ];
        if ($type === 'drug-schedules') {
            $rules['description'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
            if ($modelClass::where('slug', $validated['slug'])->where('id', '!=', $item->id)->exists()) {
                $validated['slug'] .= '-' . Str::random(4);
            }
        }

        $oldValues = $item->only(array_keys($validated));
        $item->update($validated);
        ActivityLog::log("{$singular}.updated", $singular, $item->id, $oldValues, $validated);

        return response()->json([$singular => $item->fresh()]);
    }

    public function destroy(string $type, int $id): JsonResponse
    {
        [$modelClass, , $singular, $productColumn] = $this->resolve($type);
        $item = $modelClass::findOrFail($id);

        // Check if any products reference this value
        $productCount = Product::where($productColumn, $item->name)->count();
        if ($productCount > 0) {
            return response()->json(['message' => "Cannot delete — {$productCount} products use this {$singular}"], 422);
        }

        $item->delete();
        ActivityLog::log("{$singular}.deleted", $singular, $item->id);

        return response()->json(['message' => ucfirst(str_replace('_', ' ', $singular)) . ' deleted']);
    }
}
