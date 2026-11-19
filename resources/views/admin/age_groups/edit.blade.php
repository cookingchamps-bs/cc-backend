@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Edit Age Group'">
<form action="{{ route('age-group.update', encrypt($ageGroup->id)) }}" name="edit-age-group" id="main" 
                            method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('age_from') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">From*</label>
                                        <input
                                            class="form-control {{ $errors->has('age_from') ? 'form-control-danger' : '' }}"
                                            name="age_from" type="text"
                                            value="{{ old('age_from', $ageGroup->age_from) }}" placeholder="From....">      
                                        @error('age_from')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('age_to') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">To*</label>
                                        <input
                                            class="form-control {{ $errors->has('age_to') ? 'form-control-danger' : '' }}"
                                            name="age_to" type="text"
                                            value="{{ old('age_to', $ageGroup->age_to) }}" placeholder="To....">      
                                        @error('age_to')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('age-group.index')" />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

