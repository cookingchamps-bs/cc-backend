@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title']" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['title']">
                        <div class="table-responsive">
                            <table id="subscription" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Product Id</th>
                                        <th>Price Id</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($subscription as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td>{{ $value->name }}</td>
                                        <td>{{ $value->type }}</td>
                                        <td>{{ $value->stripe_product_id }}</td>
                                        <td>{{ $value->stripe_price_id }}</td>
                                        <td>{{ $value->amount }}</td>
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :edit-url="route('subscription.edit', encrypt($value['id']))"
                                                delete-record="subscription/delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Subscription"
                                                delete-title="Delete Subscription Page"
                                            />
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
