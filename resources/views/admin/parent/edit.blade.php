@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Edit Parent'">
<form action="{{ route('parent.update', $users->id)}}" name="PrivacyPoliciesDetailForm" id="main" 
                            method="post" enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('first_name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">First Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('first_name') ? 'form-control-danger' : '' }}"
                                            name="first_name" type="text"
                                            value="{{ old('first_name', $users['first_name']) }}" placeholder="Enter first name">      
                                        @error('first_name')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('last_name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Last Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('last_name') ? 'form-control-danger' : '' }}"
                                            name="last_name" type="text"
                                            value="{{ old('last_name', $users['last_name']) }}" placeholder="Enter last name">      
                                        @error('last_name')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('email') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Email*</label>
                                        <input
                                            class="form-control {{ $errors->has('email') ? 'form-control-danger' : '' }}"
                                            name="email" type="text"
                                            value="{{ old('email', $users['email']) }}" placeholder="Enter email">      
                                        @error('email')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('role') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Role*</label>
                                        <input
                                            class="form-control {{ $errors->has('role') ? 'form-control-danger' : '' }}"
                                            name="role" type="text" disabled
                                            value="{{ old('role', $users['role']) }}" placeholder="Enter Role">      
                                        @error('role')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('image') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Image*</label>
                                        <input type="file"
                                            class="form-control {{ $errors->has('image') ? 'form-control-danger' : '' }}"
                                            onchange="loadFile(event,'image_1')" name="image">
                                        @error('image')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="media-left">
                                        <a href="#" class="profile-image">
                                            <img class="user-img img-css" id="image_1" width="30%;"
                                                src="{{ asset($users->image) }}">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('parent.index')" />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

