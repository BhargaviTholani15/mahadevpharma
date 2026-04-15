<x-layouts.app title="Delivery Agents">
<div class="space-y-6" x-data="{ showModal: false, editId: null, editName: '', editPhone: '', editVehicle: '', editType: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Delivery Agents</h1><p class="text-gray-500 mt-1">Manage delivery personnel</p></div>
        <button @click="showModal = true; editId = null; editName = ''; editPhone = ''; editVehicle = ''; editType = ''" class="btn-primary">+ Add Agent</button>
    </div>
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Name</th><th>Phone</th><th>Vehicle</th><th>Available</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($agents as $a)
                <tr>
                    <td class="font-medium text-gray-900">{{ $a->name }}</td><td>{{ $a->phone }}</td><td>{{ $a->vehicle_number ?? '-' }} {{ $a->vehicle_type ? "({$a->vehicle_type})" : '' }}</td>
                    <td><x-status-badge :status="$a->is_available ? 'ACTIVE' : 'INACTIVE'" /></td>
                    <td><div class="flex gap-1">
                        <button @click="showModal = true; editId = {{ $a->id }}; editName = '{{ addslashes($a->name) }}'; editPhone = '{{ $a->phone }}'; editVehicle = '{{ $a->vehicle_number ?? '' }}'; editType = '{{ $a->vehicle_type ?? '' }}'" class="btn-secondary btn-sm">Edit</button>
                        <form method="POST" action="{{ route('delivery-agents.destroy', $a) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No agents found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $agents->links() }}</div>
    </div>
    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-md" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit Agent' : 'Add Agent'"></h3></div>
            <form :action="editId ? '/delivery-agents/' + editId : '{{ route('delivery-agents.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="form-label">Name *</label><input type="text" name="name" x-model="editName" required class="form-input"></div>
                <div><label class="form-label">Phone *</label><input type="text" name="phone" x-model="editPhone" required class="form-input"></div>
                <div><label class="form-label">Vehicle Number</label><input type="text" name="vehicle_number" x-model="editVehicle" class="form-input"></div>
                <div><label class="form-label">Vehicle Type</label><input type="text" name="vehicle_type" x-model="editType" class="form-input" placeholder="e.g., Two Wheeler, Tempo"></div>
                <div class="flex justify-end gap-3 pt-2"><button type="button" @click="showModal = false" class="btn-secondary">Cancel</button><button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button></div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
