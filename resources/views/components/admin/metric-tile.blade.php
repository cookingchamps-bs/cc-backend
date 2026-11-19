@props([
    'label',
    'value',
    'meta' => null,
    'icon' => 'fa-chart-line',
    'accent' => false,
])

<div class="cc-metric {{ $accent ? 'cc-metric--accent' : '' }}">
    <div class="cc-metric__label">{{ $label }}</div>
    <div class="cc-metric__value">{{ $value }}</div>
    @if($meta)
        <div class="cc-metric__meta">{{ $meta }}</div>
    @endif
    <i class="cc-metric__icon fas {{ $icon }}"></i>
</div>
