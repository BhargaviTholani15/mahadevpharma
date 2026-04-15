@props(['name', 'title' => '', 'maxWidth' => 'lg'])
@php
    $widths = ['sm' => 'max-w-sm', 'md' => 'max-w-md', 'lg' => 'max-w-lg', 'xl' => 'max-w-xl', '2xl' => 'max-w-2xl'];
    $w = $widths[$maxWidth] ?? 'max-w-lg';
@endphp
<div x-show="{{ $name }}" x-transition.opacity class="modal-overlay" @click.self="{{ $name }} = false" style="display:none">
    <div class="modal-content {{ $w }}" @click.stop>
        @if($title)
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        </div>
        @endif
        <div class="px-6 py-4">
            {{ $slot }}
        </div>
    </div>
</div>
