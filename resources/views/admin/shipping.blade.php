@extends('admin.layouts.master')
@section('content')

<div class="content-wrapper">
    <x-admin.page-header title="Manage Shipping Charge" :home-url="url('admin/dashboard')" />

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Manage Shipping Charge">
                        <form action="{{route('store.shippingfee')}}" method="POST">
                            @csrf

                            <div class="row">

                                {{-- Shipping value --}}
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Shipping Charge</label>
                                        <input type="text"
                                               name="shipping_per"
                                               id="shipping_per"
                                               value="{{ old('shipping_per', $data->shipping_charges ?? '') }}"
                                               class="form-control">
                                    </div>
                                </div>

                            </div>

                            <x-admin.form-actions submit-label="Save Shipping" :cancel-url="url('admin/dashboard')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>

</div>

@endsection
