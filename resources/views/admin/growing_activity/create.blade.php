@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Create Growing Activity'">
                        <form action="{{ route('growing-activity.store') }}" name="growingActivityForm" id="growing-activity-form"
                            class="cc-encode-richtext"
                            method="post" enctype="multipart/form-data">
                            @csrf
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <strong>Please fix the following:</strong>
                                    <ul class="mb-0 mt-2">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('title') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Title*</label>
                                        <input
                                            class="form-control {{ $errors->has('title') ? 'form-control-danger' : '' }}"
                                            name="title" type="text"
                                            value="{{ old('title') }}" placeholder="Enter Growing Activity" required>
                                        @error('title')
                                            <div class="col-form-alert-label">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('image') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Image*</label>
                                        <input type="file"
                                            class="form-control {{ $errors->has('image') ? 'form-control-danger' : '' }}"
                                            onchange="loadFile(event,'image_1')" name="image"
                                            accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" required>
                                        <small class="text-muted">JPEG, PNG, or WebP up to 10MB</small>
                                        @error('image')
                                            <div class="col-form-alert-label">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="media-left mt-2">
                                        <img class="user-img img-css d-none" id="image_1" width="120" alt="Preview"
                                            src="">
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('description') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Description*</label>
                                        <textarea class="form-control summernote {{ $errors->has('description') ? 'form-control-danger' : '' }}"
                                            name="description"
                                            placeholder="Enter description">{!! old('description') ? \App\Helpers\HtmlSanitizer::richText(old('description')) : '' !!}</textarea>
                                        @error('description')
                                            <div class="col-form-alert-label">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('growing-activity.index')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
