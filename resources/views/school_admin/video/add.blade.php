@extends('school_admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="{{ $title ?? 'Upload video' }}" :home-url="route('school.dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Upload video">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif
                        <form action="{{ route('store.videos') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Video title</label>
                                        <input type="text" name="title"
                                               class="form-control @error('title') is-invalid @enderror"
                                               placeholder="Enter video title" value="{{ old('title') }}">
                                        @error('title')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Video description</label>
                                        <input type="text" name="description"
                                               class="form-control @error('description') is-invalid @enderror"
                                               placeholder="Enter video description" value="{{ old('description') }}">
                                        @error('description')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Upload video <span class="text-danger">*</span></label>
                                        <input type="file" name="video" accept="video/*"
                                               class="form-control @error('video') is-invalid @enderror"
                                               onchange="previewVideo(event)">
                                        @error('video')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Video thumbnail <span class="text-danger">*</span></label>
                                        <input type="file" name="thumbnail"
                                               class="form-control @error('thumbnail') is-invalid @enderror">
                                        @error('thumbnail')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <video id="videoPreview" width="300" height="200" controls
                                           style="display:none;border:1px solid #E5E7EB;border-radius:8px;"></video>
                                </div>
                            </div>
                            <x-admin.form-actions submit-label="Upload video" :cancel-url="route('get.video')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('script')
<script>
function previewVideo(event) {
    const video = document.getElementById('videoPreview');
    video.src = URL.createObjectURL(event.target.files[0]);
    video.style.display = 'block';
}
</script>
@endpush
