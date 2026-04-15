<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('name')->get();

        return response()->json(['roles' => $roles]);
    }

    public function show(Role $role): JsonResponse
    {
        $role->load('permissions');

        return response()->json(['role' => $role]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:roles,slug',
            'description' => 'nullable|string|max:255',
        ]);

        $role = Role::create($validated);
        ActivityLog::log('role.created', 'role', $role->id, null, $validated);

        return response()->json(['role' => $role], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        if ($role->is_system) {
            $validated = $request->validate([
                'description' => 'nullable|string|max:255',
            ]);
        } else {
            $validated = $request->validate([
                'name' => 'string|max:100',
                'slug' => 'string|max:100|unique:roles,slug,' . $role->id,
                'description' => 'nullable|string|max:255',
            ]);
        }

        $oldValues = $role->only(array_keys($validated));
        $role->update($validated);
        ActivityLog::log('role.updated', 'role', $role->id, $oldValues, $validated);

        return response()->json(['role' => $role->fresh()]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->is_system) {
            return response()->json(['message' => 'Cannot delete system roles'], 403);
        }

        if ($role->users()->exists()) {
            return response()->json(['message' => 'Cannot delete role with assigned users'], 422);
        }

        $role->permissions()->detach();
        $role->delete();
        ActivityLog::log('role.deleted', 'role', $role->id);

        return response()->json(['message' => 'Role deleted']);
    }

    public function syncPermissions(Request $request, Role $role): JsonResponse
    {
        $validated = $request->validate([
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $oldPermissions = $role->permissions()->pluck('permissions.id')->toArray();
        $role->permissions()->sync($validated['permission_ids']);
        $newPermissions = $role->permissions()->pluck('permissions.id')->toArray();

        ActivityLog::log('role.permissions_synced', 'role', $role->id,
            ['permission_ids' => $oldPermissions],
            ['permission_ids' => $newPermissions]
        );

        return response()->json(['role' => $role->load('permissions')]);
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::orderBy('module')->orderBy('name')->get();

        return response()->json(['permissions' => $permissions]);
    }
}
