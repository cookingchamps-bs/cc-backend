@props([
    'id',
    'status' => 0,
])

@php
    $isActive = (int) $status === 1;
@endphp

<a href="javascript:void(0)" id="{{ $id }}" {{ $attributes }}>
    @if($isActive)
        <i class="fas fa-toggle-on" status="Active"></i>
    @else
        <i class="fas fa-toggle-off" status="Inactive"></i>
    @endif
</a>
