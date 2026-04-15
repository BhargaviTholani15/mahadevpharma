<x-layouts.app title="Warehouses">
<div class="space-y-6" x-data="{
    showModal: false,
    editId: null,
    form: { name: '', code: '', state_code: '', address_line1: '', city: '', state: '', pincode: '', is_active: true },
    resetForm() {
        this.form = { name: '', code: '', state_code: '', address_line1: '', city: '', state: '', pincode: '', is_active: true };
    },
    openAdd() { this.resetForm(); this.editId = null; this.showModal = true; },
    openEdit(w) {
        this.editId = w.id;
        this.form = {
            name: w.name || '', code: w.code || '', state_code: w.state_code || '',
            address_line1: w.address_line1 || '', city: w.city || '',
            state: w.state || '', pincode: w.pincode || '', is_active: w.is_active
        };
        this.showModal = true;
    }
}">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Warehouses</h1><p class="text-gray-500 mt-1">Manage storage locations</p></div>
        <button @click="openAdd()" class="btn-primary">+ Add Warehouse</button>
    </div>

    <div class="card">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, city..." class="form-input"></div>
            <button type="submit" class="btn-primary">Search</button>
            @if(request('search'))<a href="{{ route('warehouses.index') }}" class="btn-secondary">Clear</a>@endif
        </form>
    </div>

    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="data-table w-full">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>State Code</th>
                        <th>City</th>
                        <th>State</th>
                        <th>Pincode</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($warehouses as $w)
                    <tr>
                        <td class="font-medium text-gray-900">{{ $w->name }}</td>
                        <td class="font-mono text-xs">{{ $w->code }}</td>
                        <td class="font-mono text-xs">{{ $w->state_code }}</td>
                        <td>{{ $w->city }}</td>
                        <td>{{ $w->state }}</td>
                        <td>{{ $w->pincode }}</td>
                        <td><x-status-badge :status="$w->is_active ? 'ACTIVE' : 'INACTIVE'" /></td>
                        <td>
                            <div class="flex gap-1">
                                <button @click="openEdit({{ $w->toJson() }})" class="btn-secondary btn-sm">Edit</button>
                                <form method="POST" action="{{ route('warehouses.destroy', $w) }}" onsubmit="return confirm('Delete this warehouse?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center py-8 text-gray-400">No warehouses found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">{{ $warehouses->links() }}</div>
    </div>

    {{-- Add/Edit Modal --}}
    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-2xl" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit Warehouse' : 'Add Warehouse'"></h3></div>
            <form :action="editId ? '{{ url('warehouses') }}/' + editId : '{{ route('warehouses.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div><label class="form-label">Name *</label><input type="text" name="name" x-model="form.name" required class="form-input"></div>
                    <div><label class="form-label">Code *</label><input type="text" name="code" x-model="form.code" required class="form-input"></div>
                    <div><label class="form-label">State Code *</label><input type="text" name="state_code" x-model="form.state_code" required maxlength="2" class="form-input"></div>
                    <div class="flex items-center gap-2 pt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" x-bind:checked="form.is_active" class="rounded border-gray-300 text-green-600 focus:ring-green-500" id="warehouse_active">
                        <label for="warehouse_active" class="form-label mb-0">Active</label>
                    </div>
                    <div class="md:col-span-2"><label class="form-label">Address *</label><input type="text" name="address_line1" x-model="form.address_line1" required class="form-input"></div>
                    <div><label class="form-label">City *</label><input type="text" name="city" x-model="form.city" required class="form-input"></div>
                    <div><label class="form-label">State *</label><input type="text" name="state" x-model="form.state" required class="form-input"></div>
                    <div><label class="form-label">Pincode *</label><input type="text" name="pincode" x-model="form.pincode" required maxlength="6" class="form-input"></div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
