<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with('role:id,name,slug');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($roleId = $request->get('role_id')) {
            $query->where('role_id', $roleId);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('full_name')->paginate($request->get('per_page', 25));

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load(['role', 'client']);
        return response()->json(['user' => $user]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:150',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|max:15|unique:users,phone',
            'password' => 'required|string|min:8',
            'role_id' => 'required|exists:roles,id',
            'is_active' => 'boolean',
        ]);

        $user = User::create($validated);
        ActivityLog::log('user.created', 'user', $user->id, null, $validated);

        return response()->json(['user' => $user->load('role')], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'string|max:150',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'string|max:15|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:6',
            'role_id' => 'exists:roles,id',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $oldValues = $user->only(array_keys($validated));
        $user->update($validated);
        ActivityLog::log('user.updated', 'user', $user->id, $oldValues, $validated);

        return response()->json(['user' => $user->fresh()->load('role')]);
    }

    public function destroy(User $user): JsonResponse
    {
        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Cannot delete your own account'], 403);
        }

        $user->delete();
        ActivityLog::log('user.deleted', 'user', $user->id);

        return response()->json(['message' => 'User deleted']);
    }

    public function toggleActive(User $user): JsonResponse
    {
        $user->update(['is_active' => !$user->is_active]);
        ActivityLog::log('user.toggled_active', 'user', $user->id, null, ['is_active' => $user->is_active]);

        return response()->json(['user' => $user]);
    }
}
