<x-layouts.app title="Categories">
<div class="space-y-6" x-data="{ showModal: false, editId: null, editName: '', editSort: 0, editActive: true }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Categories</h1><p class="text-gray-500 mt-1">Manage product categories</p></div>
        <button @click="showModal = true; editId = null; editName = ''; editSort = 0; editActive = true" class="btn-primary">+ Add Category</button>
    </div>
    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search categories..." class="form-input"></div><button type="submit" class="btn-primary">Search</button>@if(request('search'))<a href="{{ route('categories.index') }}" class="btn-secondary">Clear</a>@endif</form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Name</th><th>Slug</th><th>Products</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="font-medium text-gray-900">{{ $item->name }}</td>
                    <td class="font-mono text-xs text-gray-500">{{ $item->slug }}</td>
                    <td>{{ $item->products_count ?? 0 }}</td>
                    <td>{{ $item->sort_order }}</td>
                    <td><x-status-badge :status="$item->is_active ? 'ACTIVE' : 'INACTIVE'" /></td>
                    <td><div class="flex gap-1">
                        <button @click="showModal = true; editId = {{ $item->id }}; editName = '{{ addslashes($item->name) }}'; editSort = {{ $item->sort_order }}; editActive = {{ $item->is_active ? 'true' : 'false' }}" class="btn-secondary btn-sm">Edit</button>
                        <form method="POST" action="{{ route('categories.destroy', $item) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                    </div></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No categories found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>

    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-md" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit Category' : 'Add Category'"></h3></div>
            <form :action="editId ? '/categories/' + editId : '{{ route('categories.store') }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="form-label">Name *</label><input type="text" name="name" x-model="editName" required class="form-input"></div>
                <div><label class="form-label">Sort Order</label><input type="number" name="sort_order" x-model="editSort" class="form-input"></div>
                <div><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" x-bind:checked="editActive" class="rounded border-gray-300 text-green-600"> Active</label></div>
                <div class="flex justify-end gap-3 pt-2"><button type="button" @click="showModal = false" class="btn-secondary">Cancel</button><button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button></div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
