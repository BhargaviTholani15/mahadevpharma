<x-layouts.app title="Invoices">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Invoices</h1><p class="text-gray-500 mt-1">View and manage invoices</p></div>
    <div class="card"><form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice # or client..." class="form-input"></div>
        <div class="w-40"><select name="status" class="form-select"><option value="">All Status</option>@foreach(['DRAFT','ISSUED','PARTIALLY_PAID','PAID','CANCELLED','CREDIT_NOTE'] as $s)<option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ $s }}</option>@endforeach</select></div>
        <div class="w-40"><input type="date" name="from" value="{{ request('from') }}" class="form-input"></div>
        <div class="w-40"><input type="date" name="to" value="{{ request('to') }}" class="form-input"></div>
        <button type="submit" class="btn-primary">Apply</button>@if(request()->hasAny(['search','status','from','to']))<a href="{{ url()->current() }}" class="btn-secondary">Clear</a>@endif
    </form></div>
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Invoice #</th><th>Client</th><th>Amount</th><th>Balance</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody>
                @forelse($invoices as $inv)
                <tr>
                    <td><a href="{{ auth()->user()->isClient() ? route('vendor.invoices.show', $inv) : route('invoices.show', $inv) }}" class="font-medium text-green-600 hover:underline">{{ $inv->invoice_number }}</a></td>
                    <td>{{ $inv->client?->business_name ?? '-' }}</td>
                    <td>₹{{ number_format($inv->grand_total, 2) }}</td>
                    <td class="{{ $inv->balance_due > 0 ? 'text-red-600 font-semibold' : '' }}">₹{{ number_format($inv->balance_due, 2) }}</td>
                    <td><x-status-badge :status="$inv->status" /></td>
                    <td>{{ $inv->created_at->format('d M Y') }}</td>
                    <td><div class="flex gap-1"><a href="{{ route('invoices.show', $inv) }}" class="btn-secondary btn-sm">View</a><a href="{{ route('invoices.pdf', $inv) }}" class="btn-secondary btn-sm">PDF</a></div></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-8 text-gray-400">No invoices found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $invoices->links() }}</div>
    </div>
</div>
</x-layouts.app>
