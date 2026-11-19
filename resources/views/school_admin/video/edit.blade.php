@extends('school_admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="{{ $title ?? 'Edit video' }}" :home-url="route('school.dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Edit video">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif
                        <form action="{{ route('school.videos.update', $video->id) }}"
                              method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Video title</label>
                                        <input type="text" name="title"
                                               class="form-control @error('title') is-invalid @enderror"
                                               value="{{ old('title', $video->title) }}"
                                               placeholder="Enter video title">
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
                                               value="{{ old('description', $video->description) }}"
                                               placeholder="Enter video description">
                                        @error('description')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Upload video</label>
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
                                        <label>Status</label>
                                        <select name="status"
                                                class="form-control @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', $video->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ old('status', $video->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                        @error('status')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3">
                                    <video id="videoPreview" width="300" height="200" controls
                                           style="border:1px solid #E5E7EB;border-radius:8px;">
                                        <source src="{{ asset('storage/'.$video->video_path) }}" type="video/mp4">
                                    </video>
                                </div>
                            </div>
                            <x-admin.form-actions submit-label="Update video" :cancel-url="route('get.video')" />
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
    video.load();
}
</script>
@endpush
