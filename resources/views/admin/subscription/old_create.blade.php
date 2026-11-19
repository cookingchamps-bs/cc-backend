@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $common['title'] }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $common['title'] }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title nofloat"> <span>{{ $common['heading_title']}} </span>
                            </h3>
                        </div>
                        <div class="card-body">
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
                                        <label class="col-form-label">Subscription Type (Monthly/Yearly)*</label>
                                        <input
                                            class="form-control {{ $errors->has('type') ? 'form-control-danger' : '' }}"
                                            name="type" type="text"
                                            value="{{ old('type') }}" placeholder="Enter Subscription Type">      
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
                                {{--<div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('description') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Description*</label>
                                        <textarea class="form-control" name="description" row="5" placeholder="Enter Description"></textarea>
                                        @error('description')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>--}}
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="col-form-label">Subscription Details*</label>
                                    <div id="subscription-fields">
                                        <div class="form-group mb-3 d-flex align-items-center">
                                            <input class="form-control" name="title[]" type="text" value="{{ old('title.0') }}" placeholder="Enter Subscription Details">
                                            <button type="button" class="btn btn-danger ms-2 remove-field" style="display: none;">Remove</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary mt-2" id="add-more">Add More</button>
                                </div>
                            </div>
                            <div class="card-footer"> <button type="submit" value="submit" class="btn btn-primary">{{$common['button']}}</button> </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

