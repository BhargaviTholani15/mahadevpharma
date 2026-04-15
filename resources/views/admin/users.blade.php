<x-layouts.app title="User Management">
<div class="space-y-6" x-data="{ showModal: false, editId: null, editName: '', editEmail: '', editPhone: '', editRole: '', editPass: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">User Management</h1><p class="text-gray-500 mt-1">Manage system users</p></div>
        <button @click="showModal = true; editId = null; editName = ''; editEmail = ''; editPhone = ''; editRole = ''; editPass = ''" class="btn-primary">+ Add User</button>
    </div>
    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or phone..." class="form-input"></div><button type="submit" class="btn-primary">Search</button></form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Name</th><th>Phone</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($users as $u)
                <tr>
                    <td class="font-medium text-gray-900">{{ $u->full_name }}</td><td>{{ $u->phone }}</td><td>{{ $u->email ?? '-' }}</td>
                    <td><span class="badge-blue">{{ $u->role?->name ?? '-' }}</span></td>
                    <td><x-status-badge :status="$u->is_active ? 'ACTIVE' : 'INACTIVE'" /></td>
                    <td><div class="flex gap-1">
                        <button @click="showModal = true; editId = {{ $u->id }}; editName = '{{ addslashes($u->full_name) }}'; editEmail = '{{ $u->email ?? '' }}'; editPhone = '{{ $u->phone }}'; editRole = '{{ $u->role_id }}'; editPass = ''" class="btn-secondary btn-sm">Edit</button>
                        <form method="POST" action="{{ route('users.toggle-active', $u) }}">@csrf<button class="btn-secondary btn-sm">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                        <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No users found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $users->links() }}</div>
    </div>

    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-md" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit User' : 'Add User'"></h3></div>
            <form :action="editId ? '/users/' + editId : '{{ route('users.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="form-label">Full Name *</label><input type="text" name="full_name" x-model="editName" required class="form-input"></div>
                <div><label class="form-label">Phone *</label><input type="text" name="phone" x-model="editPhone" required class="form-input"></div>
                <div><label class="form-label">Email</label><input type="email" name="email" x-model="editEmail" class="form-input"></div>
                <div><label class="form-label">Role *</label><select name="role_id" x-model="editRole" required class="form-select">
                    <option value="">Select role</option>@foreach($roles as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach</select></div>
                <div><label class="form-label" x-text="editId ? 'New Password (leave blank to keep)' : 'Password *'"></label><input type="password" name="password" x-model="editPass" :required="!editId" class="form-input" minlength="6"></div>
                <div class="flex justify-end gap-3 pt-2"><button type="button" @click="showModal = false" class="btn-secondary">Cancel</button><button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button></div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
