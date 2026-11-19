@props([
    'viewUrl' => null,
    'editUrl' => null,
    'deleteRecord' => null,
    'deleteId' => null,
    'deleteName' => 'Record',
    'deleteTitle' => null,
])

<div class="cc-actions">
    @if($viewUrl)
        <a href="{{ $viewUrl }}" title="View"><i class="fa-solid fa-eye"></i></a>
    @endif
    @if($editUrl)
        <a href="{{ $editUrl }}" title="Edit"><i class="fa-solid fa-pencil"></i></a>
    @endif
    @if($deleteRecord && $deleteId)
        <a href="javascript:void(0)"
           record="{{ $deleteRecord }}"
           record_id="{{ $deleteId }}"
           class="confirmDelete cc-action-delete"
           name="{{ $deleteName }}"
           title="{{ $deleteTitle ?? 'Delete ' . $deleteName }}">
            <i class="fa-solid fa-trash"></i>
            <input type="hidden" id="rootUrl" value="{{ config('app.url') }}">
        </a>
    @endif
    {{ $slot }}
</div>
