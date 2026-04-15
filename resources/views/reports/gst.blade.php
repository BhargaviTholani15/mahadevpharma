<x-layouts.app title="GST Reports">
<div class="space-y-6">
    <div><h1 class="text-2xl font-bold text-gray-900">GST Reports</h1><p class="text-gray-500 mt-1">GST compliance reports</p></div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach([['GSTR-1', 'Outward supply details'], ['GSTR-3B', 'Monthly summary return'], ['HSN Summary', 'HSN-wise tax summary']] as [$title, $desc])
        <div class="card hover:shadow-md transition cursor-pointer">
            <h3 class="font-semibold text-gray-900 mb-2">{{ $title }}</h3>
            <p class="text-sm text-gray-500">{{ $desc }}</p>
        </div>
        @endforeach
    </div>
</div>
</x-layouts.app>
