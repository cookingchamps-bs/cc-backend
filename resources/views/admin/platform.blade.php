@extends('admin.layouts.master')
@section('content')

<div class="content-wrapper">
    <x-admin.page-header title="Manage Platform Charge" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Manage Platform Charge">
                        <form action="{{route('store.platformfee')}}" method="post">
                            @csrf

                            <div class="row">

                                {{-- Platform Charge --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Platform Charge (Flat)</label>
                                        <input type="text"
                                               name="platform_charge"
                                               id="platform_charge"
                                               value="{{ old('platform_charge', $data->platform_fee ?? '') }}"
                                               class="form-control">
                                    </div>
                                </div>

                            </div>

                            <x-admin.form-actions submit-label="Save Platform Charge" :cancel-url="url('admin/dashboard')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection
