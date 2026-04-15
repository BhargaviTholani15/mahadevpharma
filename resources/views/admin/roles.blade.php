<x-layouts.app title="Roles & Permissions">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Roles & Permissions</h1><p class="text-gray-500 mt-1">Manage user roles and their permissions</p></div>
    @foreach($roles as $role)
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <div><h3 class="font-semibold text-gray-900">{{ $role->name }}</h3><p class="text-sm text-gray-500">{{ $role->users_count }} users</p></div>
        </div>
        <form method="POST" action="{{ route('roles.sync-permissions', $role) }}">
            @csrf
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
                @foreach($permissions as $perm)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="permission_ids[]" value="{{ $perm->id }}" {{ $role->permissions->contains($perm->id) ? 'checked' : '' }} class="rounded border-gray-300 text-green-600">
                    {{ $perm->name }}
                </label>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t"><button type="submit" class="btn-primary btn-sm">Update Permissions</button></div>
        </form>
    </div>
    @endforeach
</div>
</x-layouts.app>
