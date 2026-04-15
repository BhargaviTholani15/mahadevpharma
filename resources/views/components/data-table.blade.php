@props(['headers' => [], 'empty' => 'No data found'])
<div class="card overflow-hidden p-0">
    <div class="overflow-x-auto">
        <table class="data-table w-full">
            <thead class="bg-gray-50/50">
                <tr>
                    @foreach($headers as $header)
                    <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
    @if(isset($pagination))
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $pagination }}
    </div>
    @endif
</div>
