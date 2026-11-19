@extends('school_admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="Dashboard" :home-url="route('school.dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <x-admin.flash />
            <div class="row">
                <div class="col-12 col-sm-6 col-md-4 mb-3">
                    <x-admin.metric-tile
                        label="Videos"
                        :value="$data"
                        meta="Uploaded for your school"
                        icon="fa-video"
                        :accent="true"
                    />
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('script')
<script>
    $(function () { $('.alert-dismissible').fadeOut(4000); });
</script>
@endpush
