@extends('admin.layouts.master')

@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$title ?? 'Edit School'" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$title ?? 'Edit School'">
                        <form action="{{ route('school.update', $school->id) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="row">

                                {{-- School Name --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>School Name <span class="text-danger">*</span></label>
                                        <input type="text"
                                               name="school_name"
                                               value="{{ old('school_name', $school->school_name) }}"
                                               class="form-control @error('school_name') is-invalid @enderror">
                                        @error('school_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- School Logo --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>School Logo</label>
                                        <input type="file"
                                               name="school_logo"
                                               class="form-control @error('school_logo') is-invalid @enderror">

                                        @if($school->school_logo)
                                            <img src="{{ asset($school->school_logo) }}"
                                                 class="mt-2"
                                                 width="80">
                                        @endif

                                        @error('school_logo')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Contact Person --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Contact Person Name</label>
                                        <input type="text"
                                               name="contact_person_name"
                                               value="{{ old('contact_person_name', $school->contact_person_name) }}"
                                               class="form-control @error('contact_person_name') is-invalid @enderror">
                                        @error('contact_person_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Email (Readonly) --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input type="email"
                                               value="{{ $school->email }}"
                                               class="form-control"
                                               readonly>
                                    </div>
                                </div>

                            </div>

                            <x-admin.form-actions submit-label="Update School" :cancel-url="route('admin.get-school')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
