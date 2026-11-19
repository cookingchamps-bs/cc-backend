@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Edit Kid'">
<form action="{{ route('kid.update', $kids->id)}}" name="PrivacyPoliciesDetailForm" id="main" 
                            method="post" enctype="multipart/form-data">
                            @method('PUT')
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    @php
                                        $parentDetail = \App\Models\User::select('first_name', 'last_name')->where('id', $kids->parent_id)->first();
                                    @endphp
                                    <div class="form-group mb-3 {{ $errors->has('parent_name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Parent Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('parent_name') ? 'form-control-danger' : '' }}"
                                            name="parent_name" type="text" disabled
                                            value="{{ $parentDetail->first_name }} {{ $parentDetail->last_name }}" placeholder="Enter Parent Name">      
                                        @error('parent_name')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('name') ? 'form-control-danger' : '' }}"
                                            name="name" type="text"
                                            value="{{ old('name', $kids['name']) }}" placeholder="Enter name">      
                                        @error('name')
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
                                            value="{{ old('role', $kids['role']) }}" placeholder="Enter Role">      
                                        @error('role')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('day') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Day*</label>
                                        <input
                                            class="form-control {{ $errors->has('day') ? 'form-control-danger' : '' }}"
                                            name="day" type="text"
                                            value="{{ old('day', $kids['day']) }}" placeholder="Enter Day">      
                                        @error('day')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('month') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Month*</label>
                                        <input
                                            class="form-control {{ $errors->has('month') ? 'form-control-danger' : '' }}"
                                            name="month" type="text"
                                            value="{{ old('month', $kids['month']) }}" placeholder="Enter Month">      
                                        @error('month')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('year') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Year*</label>
                                        <input
                                            class="form-control {{ $errors->has('year') ? 'form-control-danger' : '' }}"
                                            name="year" type="text"
                                            value="{{ old('year', $kids['year']) }}" placeholder="Enter Year">      
                                        @error('year')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('grade') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Grade*</label>
                                        <input
                                            class="form-control {{ $errors->has('grade') ? 'form-control-danger' : '' }}"
                                            name="grade" type="text"
                                            value="{{ old('grade', $kids['grade']) }}" placeholder="Enter Grade">      
                                        @error('grade')
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
                                                src="{{ asset($kids->image) }}">
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('kid.index')" />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

