<x-layouts.app title="Brands">
<div class="space-y-6" x-data="{ showModal: false, editId: null, editName: '', editMfg: '' }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Brands</h1><p class="text-gray-500 mt-1">Manage pharmaceutical brands</p></div>
        <button @click="showModal = true; editId = null; editName = ''; editMfg = ''" class="btn-primary">+ Add Brand</button>
    </div>
    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search brands..." class="form-input"></div><button type="submit" class="btn-primary">Search</button>@if(request('search'))<a href="{{ route('brands.index') }}" class="btn-secondary">Clear</a>@endif</form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Name</th><th>Manufacturer</th><th>Products</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="font-medium text-gray-900">{{ $item->name }}</td>
                    <td class="text-gray-500">{{ $item->manufacturer ?? '-' }}</td>
                    <td>{{ $item->products_count ?? 0 }}</td>
                    <td><div class="flex gap-1">
                        <button @click="showModal = true; editId = {{ $item->id }}; editName = '{{ addslashes($item->name) }}'; editMfg = '{{ addslashes($item->manufacturer ?? '') }}'" class="btn-secondary btn-sm">Edit</button>
                        <form method="POST" action="{{ route('brands.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-gray-400">No brands found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>

    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-md" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit Brand' : 'Add Brand'"></h3></div>
            <form :action="editId ? '/brands/' + editId : '{{ route('brands.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="form-label">Name *</label><input type="text" name="name" x-model="editName" required class="form-input"></div>
                <div><label class="form-label">Manufacturer</label><input type="text" name="manufacturer" x-model="editMfg" class="form-input"></div>
                <div class="flex justify-end gap-3 pt-2"><button type="button" @click="showModal = false" class="btn-secondary">Cancel</button><button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button></div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
