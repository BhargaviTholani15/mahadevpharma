@props(['status'])
@php
    $s = strtoupper($status ?? 'DRAFT');
    $map = [
        'ACTIVE' => 'badge-green', 'APPROVED' => 'badge-green', 'PAID' => 'badge-green',
        'DELIVERED' => 'badge-green', 'COMPLETED' => 'badge-green', 'VERIFIED' => 'badge-green',
        'PENDING' => 'badge-yellow', 'DRAFT' => 'badge-yellow', 'PROCESSING' => 'badge-yellow',
        'PACKED' => 'badge-blue', 'SHIPPED' => 'badge-blue', 'IN_TRANSIT' => 'badge-blue',
        'OUT_FOR_DELIVERY' => 'badge-blue', 'PARTIAL' => 'badge-purple',
        'CANCELLED' => 'badge-red', 'REJECTED' => 'badge-red', 'FAILED' => 'badge-red',
        'OVERDUE' => 'badge-red', 'EXPIRED' => 'badge-red',
        'INACTIVE' => 'badge-gray', 'RETURNED' => 'badge-orange',
    ];
    $class = $map[$s] ?? 'badge-gray';
@endphp
<span class="{{ $class }}">{{ str_replace('_', ' ', $s) }}</span>
