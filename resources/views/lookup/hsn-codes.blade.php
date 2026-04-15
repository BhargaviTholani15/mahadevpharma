<x-layouts.app title="HSN Codes">
<div class="space-y-6" x-data="{ showModal: false, editId: null, editCode: '', editDesc: '', editCgst: 6, editSgst: 6, editIgst: 12, editFrom: '{{ date('Y-m-d') }}' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">HSN Codes</h1><p class="text-gray-500 mt-1">Manage HSN/SAC codes with GST rates</p></div>
        <button @click="showModal = true; editId = null; editCode = ''; editDesc = ''; editCgst = 6; editSgst = 6; editIgst = 12; editFrom = '{{ date('Y-m-d') }}'" class="btn-primary">+ Add HSN Code</button>
    </div>
    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or description..." class="form-input"></div><button type="submit" class="btn-primary">Search</button>@if(request('search'))<a href="{{ route('hsn-codes.index') }}" class="btn-secondary">Clear</a>@endif</form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Code</th><th>Description</th><th>CGST</th><th>SGST</th><th>IGST</th><th>Effective From</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="font-mono font-medium">{{ $item->code }}</td>
                    <td class="text-gray-500 max-w-xs truncate">{{ $item->description }}</td>
                    <td>{{ $item->cgst_rate }}%</td><td>{{ $item->sgst_rate }}%</td><td>{{ $item->igst_rate }}%</td>
                    <td>{{ $item->effective_from?->format('d M Y') ?? $item->effective_from }}</td>
                    <td><div class="flex gap-1">
                        <button @click="showModal = true; editId = {{ $item->id }}; editCode = '{{ $item->code }}'; editDesc = '{{ addslashes($item->description) }}'; editCgst = {{ $item->cgst_rate }}; editSgst = {{ $item->sgst_rate }}; editIgst = {{ $item->igst_rate }}; editFrom = '{{ $item->effective_from instanceof \Carbon\Carbon ? $item->effective_from->format('Y-m-d') : $item->effective_from }}'" class="btn-secondary btn-sm">Edit</button>
                        <form method="POST" action="{{ route('hsn-codes.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">No HSN codes found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>

    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-lg" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit HSN Code' : 'Add HSN Code'"></h3></div>
            <form :action="editId ? '/hsn-codes/' + editId : '{{ route('hsn-codes.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="form-label">Code *</label><input type="text" name="code" x-model="editCode" required class="form-input"></div>
                    <div><label class="form-label">Effective From *</label><input type="date" name="effective_from" x-model="editFrom" required class="form-input"></div>
                </div>
                <div><label class="form-label">Description *</label><input type="text" name="description" x-model="editDesc" required class="form-input"></div>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="form-label">CGST % *</label><input type="number" step="0.01" name="cgst_rate" x-model="editCgst" required class="form-input"></div>
                    <div><label class="form-label">SGST % *</label><input type="number" step="0.01" name="sgst_rate" x-model="editSgst" required class="form-input"></div>
                    <div><label class="form-label">IGST % *</label><input type="number" step="0.01" name="igst_rate" x-model="editIgst" required class="form-input"></div>
                </div>
                <div class="flex justify-end gap-3 pt-2"><button type="button" @click="showModal = false" class="btn-secondary">Cancel</button><button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button></div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
