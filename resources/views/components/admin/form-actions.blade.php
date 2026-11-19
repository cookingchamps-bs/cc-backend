@props([
    'submitLabel' => 'Save',
    'cancelUrl' => null,
    'cancelLabel' => 'Cancel',
])

<div class="cc-form-actions">
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
    @if($cancelUrl)
        <a href="{{ $cancelUrl }}" class="btn btn-secondary">{{ $cancelLabel }}</a>
    @endif
    {{ $slot }}
</div>
