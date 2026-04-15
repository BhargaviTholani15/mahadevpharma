<x-layouts.app title="Audit Logs">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Audit Logs</h1><p class="text-gray-500 mt-1">System activity trail</p></div>
    <div class="card"><form method="GET" class="flex gap-3"><div class="flex-1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search actions..." class="form-input"></div><button type="submit" class="btn-primary">Search</button></form></div>
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Action</th><th>Entity</th><th>User</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="font-medium">{{ $log->action }}</td>
                    <td class="text-gray-500">{{ $log->entity_type }} #{{ $log->entity_id }}</td>
                    <td>{{ $log->user?->full_name ?? 'System' }}</td>
                    <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-gray-400">No logs found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
</div>
</x-layouts.app>
