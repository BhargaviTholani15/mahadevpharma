<x-layouts.app title="Clients">
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Clients</h1><p class="text-gray-500 mt-1">Manage pharmacy partners</p></div>
        <a href="{{ route('clients.create') }}" class="btn-primary">+ Add Client</a>
    </div>
    <div class="card"><form method="GET" class="flex flex-wrap gap-3 items-end"><div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, GST, contact..." class="form-input"></div>
        <div class="w-40"><select name="status" class="form-select"><option value="">All Status</option><option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>KYC Verified</option><option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option></select></div>
        <button type="submit" class="btn-primary">Filter</button>@if(request()->hasAny(['search','status']))<a href="{{ route('clients.index') }}" class="btn-secondary">Clear</a>@endif</form></div>

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Business Name</th><th>Proprietor</th><th>Contact</th><th>GST</th><th>KYC</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($clients as $c)
                <tr>
                    <td><a href="{{ route('clients.show', $c) }}" class="font-medium text-gray-900 hover:text-green-600">{{ $c->business_name }}</a></td>
                    <td>{{ $c->proprietor_name ?? '-' }}</td>
                    <td>{{ $c->contact_person ?? '-' }}</td>
                    <td class="font-mono text-xs">{{ $c->gst_number ?? '-' }}</td>
                    <td><x-status-badge :status="$c->kyc_verified ? 'VERIFIED' : 'PENDING'" /></td>
                    <td><div class="flex gap-1"><a href="{{ route('clients.edit', $c) }}" class="btn-secondary btn-sm">Edit</a><a href="{{ route('clients.show', $c) }}" class="btn-secondary btn-sm">View</a></div></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No clients found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $clients->links() }}</div>
    </div>
</div>
</x-layouts.app>
