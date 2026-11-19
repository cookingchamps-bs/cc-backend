@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper admin-detail-page">
        <x-admin.page-header :title="$school->school_name" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <x-admin.data-card title="School information" class="mb-3">
                    <div class="row align-items-start">
                        <div class="col-md-3 text-center text-md-left mb-3 mb-md-0">
                            @if($school->school_logo)
                                <img src="{{ asset($school->school_logo) }}" alt="" class="img-fluid rounded border" style="max-height: 140px; object-fit: cover;">
                            @else
                                <div class="text-muted small py-4">No logo</div>
                            @endif
                        </div>
                        <div class="col-md-9">
                            <dl class="row mb-0">
                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">{{ $school->email }}</dd>
                                <dt class="col-sm-3">Contact</dt>
                                <dd class="col-sm-9">{{ $school->contact_person_name ?: '—' }}</dd>
                                <dt class="col-sm-3">Account status</dt>
                                <dd class="col-sm-9">
                                    @if($school->isAccountActive())
                                        <span class="badge badge-success">Active</span>
                                        <span class="text-muted small d-block mt-1">School can sign in.</span>
                                    @else
                                        <span class="badge badge-danger">Deactivated</span>
                                        <span class="text-muted small d-block mt-1">School cannot sign in while deactivated.</span>
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
            </x-admin.data-card>

            <x-admin.data-card :title="'Uploaded videos (' . $videos->count() . ')'">
                    @if($videos->count())
                        <div class="row">
                            @foreach($videos as $video)
                                @php
                                    $thumbUrl = $video->thumbnail_image
                                        ? asset('storage/' . ltrim($video->thumbnail_image, '/'))
                                        : null;
                                    $videoUrl = $video->video_path
                                        ? asset('storage/' . ltrim($video->video_path, '/'))
                                        : null;
                                @endphp
                                <div class="col-12 col-sm-6 col-lg-4 mb-4">
                                    <div class="card h-100 shadow-sm border">
                                        @if($thumbUrl)
                                            <img src="{{ $thumbUrl }}"
                                                 class="card-img-top"
                                                 alt=""
                                                 style="height: 180px; object-fit: cover;">
                                        @else
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted" style="height: 180px;">
                                                <i class="fas fa-video fa-3x"></i>
                                            </div>
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h6 class="font-weight-bold mb-0 pr-2">{{ $video->title }}</h6>
                                                @if(isset($video->is_active))
                                                    @if((int) $video->is_active === 1)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                @endif
                                            </div>
                                            <p class="text-muted small flex-grow-1">{{ Str::limit(strip_tags($video->description ?? ''), 120) ?: 'No description' }}</p>
                                            @if($videoUrl)
                                                <a href="{{ $videoUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary mt-auto">
                                                    <i class="fas fa-play mr-1"></i> Open video
                                                </a>
                                            @else
                                                <span class="text-muted small">No file</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted mb-0 py-5">No videos uploaded by this school yet.</p>
                    @endif
            </x-admin.data-card>
        </div>
    </section>
</div>
@endsection
