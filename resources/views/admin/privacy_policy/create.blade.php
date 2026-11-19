@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Privacy Policy'">
<form name="PrivacyPolicyForm" id="main" 
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('title') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Title*</label>
                                        <input
                                            class="form-control {{ $errors->has('title') ? 'form-control-danger' : '' }}"
                                            name="title" type="text"
                                            value="{{ old('title', $PrivacyPolicy->title ?? '-') }}" placeholder="Enter title">      
                                        @error('title')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('description') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Description*</label>
                                        <textarea
                                        class="form-control summernote {{ $errors->has('description') ? 'form-control-danger' : ''}}"
                                        name="description" type="message"
                                            placeholder="Enter Description">{{ old('description', $PrivacyPolicy->description ?? '-') }}</textarea>  
                                        @error('description')
                                            <div class="col-form-alert-label">
                                            {{$message}}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="url('admin/dashboard')" />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

