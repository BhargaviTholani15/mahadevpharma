<x-layouts.app title="Reports">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">Reports</h1><p class="text-gray-500 mt-1">Business analytics and reports</p></div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach([
            ['Sales Report', 'View revenue, orders and sales trends', 'bar-chart-3'],
            ['Outstanding Report', 'Track pending payments from clients', 'credit-card'],
            ['Aging Report', 'Analyze overdue invoices by age buckets', 'clock'],
            ['Collection Report', 'Payment collection tracking', 'indian-rupee'],
            ['Product Performance', 'Top selling products analysis', 'trending-up'],
        ] as [$title, $desc, $icon])
        <div class="card hover:shadow-md transition cursor-pointer">
            <h3 class="font-semibold text-gray-900 mb-2">{{ $title }}</h3>
            <p class="text-sm text-gray-500">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</div>
</x-layouts.app>
