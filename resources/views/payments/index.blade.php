<x-layouts.app title="Payments">
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-gray-900">Payments</h1><p class="text-gray-500 mt-1">Payment records</p></div>
        @if(!auth()->user()->isClient())<a href="{{ route('payments.create') }}" class="btn-primary">+ Record Payment</a>@endif
    </div>
    <div class="card"><form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]"><input type="text" name="search" value="{{ request('search') }}" placeholder="Receipt # or client..." class="form-input"></div>
        <div class="w-40"><input type="date" name="from" value="{{ request('from') }}" class="form-input"></div>
        <div class="w-40"><input type="date" name="to" value="{{ request('to') }}" class="form-input"></div>
        <button type="submit" class="btn-primary">Apply</button>
    </form></div>
    <div class="card overflow-hidden p-0">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50"><tr><th>Receipt #</th><th>Client</th><th>Amount</th><th>Method</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
                @forelse($payments as $p)
                <tr>
                    <td class="font-mono font-medium">{{ $p->payment_number }}</td>
                    <td>{{ $p->client?->business_name ?? '-' }}</td>
                    <td class="font-semibold text-green-600">₹{{ number_format($p->amount, 2) }}</td>
                    <td>{{ $p->payment_method }}</td>
                    <td><x-status-badge :status="$p->status ?? 'COMPLETED'" /></td>
                    <td>{{ $p->payment_date ? \Carbon\Carbon::parse($p->payment_date)->format('d M Y') : $p->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-8 text-gray-400">No payments found</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $payments->links() }}</div>
    </div>
</div>
</x-layouts.app>
