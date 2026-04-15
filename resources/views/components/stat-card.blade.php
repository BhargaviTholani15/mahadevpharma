@props(['title', 'value', 'color' => 'blue', 'icon' => ''])
<div class="stat-card">
    @if($icon)
    <div class="p-3 rounded-xl bg-{{ $color }}-50">
        <span class="text-{{ $color }}-600">{!! $icon !!}</span>
    </div>
    @endif
    <div>
        <p class="text-sm text-gray-500">{{ $title }}</p>
        <p class="text-2xl font-bold text-gray-900">{{ $value }}</p>
    </div>
</div>
