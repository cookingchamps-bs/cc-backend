@props([
    'message' => 'No records found.',
    'icon' => 'fa-inbox',
])

<div class="cc-empty">
    <i class="fa-solid {{ $icon }}"></i>
    <p class="mb-0">{{ $message }}</p>
    {{ $slot }}
</div>
