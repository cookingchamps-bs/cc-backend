@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Create Subscription'">
<form action="{{ route('subscription.store') }}" name="subscription" id="main" 
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('name') ? 'form-control-danger' : '' }}"
                                            name="name" type="text"
                                            value="{{ old('name') }}" placeholder="Enter Subscription Name">      
                                        @error('name')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('type') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Plan type*</label>
                                        <select class="form-control {{ $errors->has('type') ? 'form-control-danger' : '' }}" name="type">
                                            <option value="first-kid" {{ old('type') === 'first-kid' ? 'selected' : '' }}>First kid (Kids Learning)</option>
                                            <option value="additional-kid" {{ old('type') === 'additional-kid' ? 'selected' : '' }}>Additional kid</option>
                                        </select>
                                        @error('type')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('amount') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Amount*</label>
                                        <input
                                            class="form-control {{ $errors->has('amount') ? 'form-control-danger' : '' }}"
                                            name="amount" type="text"
                                            value="{{ old('amount') }}" placeholder="Enter Amount">      
                                        @error('amount')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('subscription.index')" />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

