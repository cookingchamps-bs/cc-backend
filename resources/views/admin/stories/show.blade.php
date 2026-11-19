@extends('admin.layouts.master')
@section('content')
@php
    $title = $story->title ?? 'Story';
    $addedBy = '—';
    if ($story->role === 'parent' && $story->storyAddedByParent) {
        $addedBy = trim(($story->storyAddedByParent->first_name ?? '') . ' ' . ($story->storyAddedByParent->last_name ?? '')) ?: '—';
    } elseif ($story->role === 'kid' && $story->storyAddedByChild) {
        $addedBy = $story->storyAddedByChild->name ?? '—';
    } elseif ($story->role === 'admin') {
        $addedBy = 'Admin';
    }
@endphp
<div class="content-wrapper admin-detail-page">
        <x-admin.page-header :title="Str::limit($title, 60)" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
@if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <x-admin.data-card title="Story information">
                <div class="mb-3 text-right">
                    <a href="{{ route('story.edit', encrypt($story->id)) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen mr-1"></i> Edit</a>
                    <a href="{{ route('story.index') }}" class="btn btn-default btn-sm border"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                </div>
                    <div class="row">
                        <div class="col-12 col-lg-8">
                            <dl class="row dl-detail">
                                <dt class="col-sm-4">Title</dt><dd class="col-sm-8">{{ $story->title ?? '—' }}</dd>
                                <dt class="col-sm-4">Added by</dt><dd class="col-sm-8">{{ $addedBy }}</dd>
                                <dt class="col-sm-4">Role</dt><dd class="col-sm-8"><span class="badge badge-secondary">{{ $story->role ?? '—' }}</span></dd>
                                <dt class="col-sm-4">Best of the champs</dt><dd class="col-sm-8">{{ $story->status == 0 ? 'No' : 'Yes' }}</dd>
                                <dt class="col-sm-4">Featured</dt><dd class="col-sm-8">{{ ($story->is_featured ?? 0) == 0 ? 'No' : 'Yes' }}</dd>
                                <dt class="col-sm-4">Video URL</dt><dd class="col-sm-8 text-break"><small>{{ $story->video_link ?: '—' }}</small></dd>
                            </dl>
                            <h6 class="text-muted text-uppercase small font-weight-bold mt-3">Description</h6>
                            <div class="border rounded p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                                {!! \App\Helpers\HtmlSanitizer::richText($story->description) ?: '<span class="text-muted">—</span>' !!}
                            </div>
                        </div>
                        <div class="col-12 col-lg-4 mt-4 mt-lg-0">
                            <h6 class="text-muted text-uppercase small font-weight-bold mb-2">Images</h6>
                            @if($story->role == 'admin')
                                <ul class="story-gallery">
                                    <li>
                                        <a target="_blank" href="{{ asset($story->image) }}">
                                            <img src="{{ $story->image != '' ? asset($story->image) : asset('uploads/placeholder/default_user.png') }}" alt="">
                                        </a>
                                    </li>
                                </ul>
                            @else
                                <ul class="story-gallery">
                                    @forelse($story->storyImages as $val)
                                        <li>
                                            <a target="_blank" href="{{ asset($val->images) }}">
                                                <img src="{{ $val->images != '' ? asset($val->images) : asset('uploads/placeholder/default_user.png') }}" alt="">
                                            </a>
                                        </li>
                                    @empty
                                        <p class="text-muted small mb-0">No gallery images.</p>
                                    @endforelse
                                </ul>
                            @endif
                        </div>
                    </div>
            </x-admin.data-card>
        </div>
    </section>
</div>
@endsection
@push('script')
<script>$(function () { $('.alert-dismissible').fadeOut(4000); });</script>
@endpush
