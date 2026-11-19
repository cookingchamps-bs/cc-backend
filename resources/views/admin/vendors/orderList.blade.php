@extends('admin.layouts.vendor_master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="Orders" :home-url="route('vendor.dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Order list">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Your total</th>
                                        <th>Your products</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $key => $order)
                                        <tr>
                                            <td class="text-center text-muted">{{ $key + 1 }}</td>
                                            <td>{{ $order->unique_id }}</td>
                                            <td>
                                                {{ $order->user->first_name ?? 'Customer' }}
                                            </td>
                                            <td>₹{{ number_format((float) ($order->vendor_subtotal ?? 0), 2) }}</td>
                                            <td>
                                                @foreach($order->items as $item)
                                                    <div>
                                                        {{ $item->product_name }}
                                                        (x{{ $item->quantity }})
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>{{ $order->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6">
                                                <x-admin.empty-state message="No orders yet" icon="fa-box" />
                                            </td>
                                        </tr>
                                    @endforelse
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
