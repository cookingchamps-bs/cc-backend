@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title'] ?? 'Add Vendor'" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Add Vendor'">
                        <form action="{{ route('vendors.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">

                                <!-- Username -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>UserName*</label>
                                        <input type="text" name="username"
                                            class="form-control @error('username') is-invalid @enderror"
                                            value="{{ old('username') }}"
                                            placeholder="Enter Username">

                                        @error('username')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                                <!-- Password -->
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Password*</label>
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Enter Password">

                                        @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name*</label>
                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            placeholder="Enter Name">

                                        @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mobile No*</label>
                                        <input type="text" name="mobile_no"
                                            class="form-control @error('mobile_no') is-invalid @enderror"
                                            placeholder="Enter Mobile No">

                                        @error('mobile_no')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company Name*</label>
                                        <input type="text" name="company_name"
                                            class="form-control @error('company_name') is-invalid @enderror"
                                            placeholder="Enter company name">

                                        @error('company_name')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                            </div>

                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('vendors.index')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection
