<x-layouts.app :title="$label . 's'">
<div class="space-y-6" x-data="{ showModal: false, editId: null, editName: '', editDesc: '', editSort: 0 }">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">{{ $label }}s</h1><p class="text-gray-500 mt-1">Manage {{ strtolower($label) }} options for products</p></div>
        <button @click="showModal = true; editId = null; editName = ''; editDesc = ''; editSort = 0" class="btn-primary">+ Add {{ $label }}</button>
    </div>

    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search..." class="form-input"></div><button type="submit" class="btn-primary">Search</button>@if(request('search'))<a href="{{ route($type.'.index', ['type' => $type]) }}" class="btn-secondary">Clear</a>@endif</form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Name</th>@if($hasDescription)<th>Description</th>@endif<th>Sort</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td class="font-medium text-gray-900">{{ $item->name }}</td>
                    @if($hasDescription)<td class="text-gray-500">{{ $item->description ?? '-' }}</td>@endif
                    <td>{{ $item->sort_order }}</td>
                    <td>
                        <div class="flex gap-1">
                            <button @click="showModal = true; editId = {{ $item->id }}; editName = '{{ addslashes($item->name) }}'; editDesc = '{{ addslashes($item->description ?? '') }}'; editSort = {{ $item->sort_order }}" class="btn-secondary btn-sm">Edit</button>
                            <form method="POST" action="{{ route($type.'.destroy', ['type' => $type, 'id' => $item->id]) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="btn-danger btn-sm">Del</button></form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="{{ $hasDescription ? 4 : 3 }}" class="text-center py-8 text-gray-400">No items found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $items->links() }}</div>
    </div>

    {{-- Modal --}}
    <div x-show="showModal" x-transition.opacity class="modal-overlay" @click.self="showModal = false" style="display:none">
        <div class="modal-content max-w-md" @click.stop>
            <div class="px-6 py-4 border-b"><h3 class="text-lg font-semibold" x-text="editId ? 'Edit {{ $label }}' : 'Add {{ $label }}'"></h3></div>
            <form :action="editId ? '{{ url($type) }}/' + editId : '{{ route($type.'.store', ['type' => $type]) }}'" method="POST" class="px-6 py-4 space-y-4">
                @csrf
                <template x-if="editId"><input type="hidden" name="_method" value="PUT"></template>
                <div><label class="form-label">Name *</label><input type="text" name="name" x-model="editName" required class="form-input"></div>
                @if($hasDescription)<div><label class="form-label">Description</label><textarea name="description" x-model="editDesc" class="form-textarea" rows="2"></textarea></div>@endif
                <div><label class="form-label">Sort Order</label><input type="number" name="sort_order" x-model="editSort" class="form-input"></div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" @click="showModal = false" class="btn-secondary">Cancel</button>
                    <button type="submit" class="btn-primary" x-text="editId ? 'Update' : 'Add'"></button>
                </div>
            </form>
        </div>
    </div>
</div>
</x-layouts.app>
