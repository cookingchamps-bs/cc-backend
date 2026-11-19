@props([
    'title' => null,
    'createUrl' => null,
    'createLabel' => 'Add',
])

<div {{ $attributes->merge(['class' => 'card cc-card']) }}>
    @if($title || $createUrl || isset($header))
        <div class="card-header">
            <x-admin.flash />
            @isset($header)
                {{ $header }}
            @else
                <h3 class="card-title nofloat">
                    <span>{{ $title }}</span>
                    @if($createUrl)
                        <span>
                            <a href="{{ $createUrl }}">
                                <button type="button" class="btn btn-primary">
                                    <i class="fa-solid fa-plus"></i>&nbsp;&nbsp;{{ $createLabel }}
                                </button>
                            </a>
                        </span>
                    @endif
                </h3>
            @endisset
        </div>
    @else
        <div class="card-header py-2">
            <x-admin.flash />
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
