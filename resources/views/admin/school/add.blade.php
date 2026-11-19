@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$title ?? 'Add School'" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$title ?? 'Add School'">
                        <form action="{{route('admin.store-school')}}" method="POST" enctype="multipart/form-data">
                            @csrf

                            <div class="row">

                                {{-- School Name --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>School Name <span class="text-danger">*</span></label>
                                        <input type="text" name="school_name"
                                               class="form-control @error('school_name') is-invalid @enderror"
                                               placeholder="Enter school name">
                                        @error('school_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- School Logo --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>School Logo</label>
                                        <input type="file" name="school_logo"
                                               class="form-control @error('school_logo') is-invalid @enderror">
                                        @error('school_logo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Contact Person --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact Person Name</label>
                                        <input type="text" name="contact_person_name"
                                               class="form-control @error('contact_person_name') is-invalid @enderror"
                                               placeholder="Enter contact person name">
                                        @error('contact_person_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email"
                                               class="form-control @error('email') is-invalid @enderror"
                                               placeholder="Enter email">
                                        @error('email')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>



                            </div>

                            <x-admin.form-actions submit-label="Save School" :cancel-url="route('admin.get-school')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
