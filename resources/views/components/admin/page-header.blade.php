@props([
    'title',
    'homeUrl' => null,
    'homeLabel' => 'Home',
])

@php
    $homeUrl = $homeUrl ?? url('admin/dashboard');
@endphp

<div class="content-header cc-page-header">
    <div class="container-fluid">
        <div class="cc-page-header__row">
            <div>
                <h1>{{ $title }}</h1>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ $homeUrl }}">{{ $homeLabel }}</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </div>
            @isset($actions)
                <div class="cc-page-header__actions">
                    {{ $actions }}
                </div>
            @endisset
        </div>
    </div>
</div>
