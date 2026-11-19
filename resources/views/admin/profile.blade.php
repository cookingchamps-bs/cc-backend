@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Profile'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Profile'">
                        <form action="{{ url('admin/profile') }}" name="AdminDetailsForm" id="main"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('name') ? 'form-control-danger' : '' }}"
                                            name="name" type="text"
                                            value="{{ $adminDetails->name }}" placeholder="Enter Name">
                                        @error('name')
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
                                            name="email" type="email"
                                            value="{{ $adminDetails->email }}" placeholder="Enter email">
                                        @error('email')
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
                                        <a target="_blank" href="{{ url($adminDetails->image)}}" class="profile-image">
                                            <img class="user-img img-css" id="image_1" width="150px;" height="150px;" style="border-radius:50%;"
                                                src="{{ $adminDetails->image !='' ? asset($adminDetails->image) : asset('uploads/placeholder/default_user.png') }}">
                                        </a>
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

