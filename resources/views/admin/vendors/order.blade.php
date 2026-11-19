@extends('admin.layouts.master')
@section('content')
@php
    $paymentLabels = [0 => 'Pending', 1 => 'Paid', 2 => 'Failed', 3 => 'Cancelled'];
    $paymentClasses = [0 => 'badge-warning', 1 => 'badge-success', 2 => 'badge-danger', 3 => 'badge-dark'];
    $deliveryLabels = [0 => 'Not started', 1 => 'Processing', 2 => 'Shipped', 3 => 'Delivered', 4 => 'Failed'];
    $deliveryClasses = [0 => 'badge-secondary', 1 => 'badge-info', 2 => 'badge-primary', 3 => 'badge-success', 4 => 'badge-danger'];
@endphp
<div class="content-wrapper">
    <x-admin.page-header title="Orders" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="All orders">
                        <div class="table-responsive">
                            <table id="growing_activity" class="table table-bordered table-hover cc-order-table">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Payment</th>
                                        <th class="text-center">Delivery</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $value)
                                    @php
                                        $customerName = trim(($value->user->first_name ?? '') . ' ' . ($value->user->last_name ?? ''));
                                        $itemCount = $value->items ? $value->items->count() : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <a href="{{ route('view.order', $value->id) }}" class="cc-order-id">
                                                {{ $value->unique_id ?? ('#' . $value->id) }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="cc-order-customer">
                                                <strong>{{ $customerName !== '' ? $customerName : 'N/A' }}</strong>
                                                @if(optional($value->user)->email)
                                                    <small>{{ $value->user->email }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center">{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</td>
                                        <td class="text-right"><strong>${{ number_format((float) $value->total_amount, 2) }}</strong></td>
                                        <td class="text-center">
                                            <span class="badge {{ $paymentClasses[$value->status] ?? 'badge-secondary' }}">
                                                {{ $paymentLabels[$value->status] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $deliveryClasses[$value->delivery_status] ?? 'badge-secondary' }}">
                                                {{ $deliveryLabels[$value->delivery_status] ?? 'Unknown' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="cc-order-date">
                                                {{ $value->created_at ? $value->created_at->format('d M Y') : '—' }}
                                            </span>
                                            @if($value->created_at)
                                                <small class="d-block text-muted">{{ $value->created_at->format('g:i A') }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <x-admin.action-buttons :view-url="route('view.order', $value->id)" />
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8">
                                            <x-admin.empty-state message="No orders yet" icon="fa-shopping-cart" />
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
