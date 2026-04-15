<x-layouts.app title="My Ledger">
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div><h1 class="text-2xl font-bold text-gray-900">My Ledger</h1><p class="text-gray-500 mt-1">Your account statement and transactions</p></div>
        @php
            $client = auth()->user()->client;
        @endphp
        @if($client)
        <div class="flex gap-4">
            <div class="card text-center px-6">
                <p class="text-sm text-gray-500">Credit Limit</p>
                <p class="text-xl font-bold text-gray-900">₹{{ number_format($client->credit_limit, 2) }}</p>
            </div>
            <div class="card text-center px-6">
                <p class="text-sm text-gray-500">Outstanding</p>
                <p class="text-xl font-bold text-red-600">₹{{ number_format($client->current_outstanding, 2) }}</p>
            </div>
        </div>
        @endif
    </div>

    @php
        $entries = $client ? \App\Models\LedgerEntry::where('client_id', $client->id)->latest('entry_date')->paginate(25) : collect();
    @endphp

    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Date</th><th>Type</th><th>Narration</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th class="text-right">Balance</th></tr></thead>
            <tbody>
                @forelse($entries as $entry)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($entry->entry_date)->format('d M Y') }}</td>
                    <td><x-status-badge :status="$entry->entry_type" /></td>
                    <td>{{ $entry->narration ?? '-' }}</td>
                    <td class="text-right {{ $entry->debit_amount > 0 ? 'text-red-600 font-semibold' : '' }}">{{ $entry->debit_amount > 0 ? '₹'.number_format($entry->debit_amount, 2) : '-' }}</td>
                    <td class="text-right {{ $entry->credit_amount > 0 ? 'text-green-600 font-semibold' : '' }}">{{ $entry->credit_amount > 0 ? '₹'.number_format($entry->credit_amount, 2) : '-' }}</td>
                    <td class="text-right font-bold">₹{{ number_format($entry->running_balance, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No ledger entries found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($entries instanceof \Illuminate\Pagination\LengthAwarePaginator && $entries->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">{{ $entries->links() }}</div>
        @endif
    </div>
</div>
</x-layouts.app>
