<x-layouts.app :title="$client->business_name">
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4"><a href="{{ route('clients.index') }}" class="btn-secondary btn-sm">&larr; Back</a><h1 class="text-2xl font-bold text-gray-900">{{ $client->business_name }}</h1><x-status-badge :status="$client->kyc_verified ? 'VERIFIED' : 'PENDING'" /></div>
        <a href="{{ route('clients.edit', $client) }}" class="btn-primary">Edit</a>
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Contact</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Proprietor</dt><dd>{{ $client->proprietor_name ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Contact Person</dt><dd>{{ $client->contact_person ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Alt Phone</dt><dd>{{ $client->alt_phone ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">GST</dt><dd class="font-mono">{{ $client->gst_number ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Drug License</dt><dd>{{ $client->drug_license_no ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">PAN</dt><dd>{{ $client->pan_number ?? '-' }}</dd></div>
            </dl>
        </div>
        <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Address & Terms</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Address</dt><dd>{{ $client->address_line1 ?? '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">City</dt><dd>{{ $client->city ?? '-' }}, {{ $client->state ?? '' }} {{ $client->pincode ?? '' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Credit Limit</dt><dd>{{ $client->credit_limit ? '₹'.number_format($client->credit_limit, 2) : '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Credit Period</dt><dd>{{ $client->credit_period_days ? $client->credit_period_days.' days' : '-' }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Outstanding</dt><dd class="font-semibold {{ ($client->current_outstanding ?? 0) > 0 ? 'text-red-600' : 'text-green-600' }}">₹{{ number_format($client->current_outstanding ?? 0, 2) }}</dd></div>
            </dl>
        </div>
    </div>
    <div class="card"><h3 class="font-semibold text-gray-900 mb-4">Ledger</h3>
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Date</th><th>Description</th><th>Type</th><th>Amount</th><th>Balance</th></tr></thead>
            <tbody>
                @forelse($ledger as $entry)
                <tr>
                    <td>{{ $entry->created_at->format('d M Y') }}</td>
                    <td>{{ $entry->description }}</td>
                    <td><span class="{{ $entry->type === 'CREDIT' ? 'badge-green' : 'badge-red' }}">{{ $entry->type }}</span></td>
                    <td>₹{{ number_format($entry->amount, 2) }}</td>
                    <td class="font-semibold">₹{{ number_format($entry->running_balance, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-8 text-gray-400">No ledger entries</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $ledger->links() }}</div>
    </div>
</div>
</x-layouts.app>
