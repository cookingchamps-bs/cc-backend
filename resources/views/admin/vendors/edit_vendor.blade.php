@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title'] ?? 'Edit Vendor'" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Edit Vendor'">
                        <form action="{{ route('vendors.update', encrypt($vendor->id)) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Username*</label>
                                        <input type="text" name="username"
                                            class="form-control @error('username') is-invalid @enderror"
                                            value="{{ old('username', $vendor->username) }}"
                                            placeholder="Enter username">
                                        @error('username')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>New password</label>
                                        <input type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror"
                                            placeholder="Leave blank to keep current password">
                                        @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Minimum 6 characters if changing.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Name*</label>
                                        <input type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name', $vendor->name) }}"
                                            placeholder="Enter name">
                                        @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Mobile number*</label>
                                        <input type="text" name="mobile_no"
                                            class="form-control @error('mobile_no') is-invalid @enderror"
                                            value="{{ old('mobile_no', $vendor->mobile_no) }}"
                                            placeholder="Enter mobile number">
                                        @error('mobile_no')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Company name*</label>
                                        <input type="text" name="company_name"
                                            class="form-control @error('company_name') is-invalid @enderror"
                                            value="{{ old('company_name', $vendor->company_name) }}"
                                            placeholder="Enter company name">
                                        @error('company_name')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <x-admin.form-actions
                                :submit-label="$common['button'] ?? 'Update'"
                                :cancel-url="route('vendors.show', encrypt($vendor->id))"
                            />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
