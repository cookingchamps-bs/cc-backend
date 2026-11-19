@extends('admin.layouts.master')
@section('content')
@php
    $paymentLabels = [0 => 'Pending', 1 => 'Paid', 2 => 'Failed', 3 => 'Cancelled'];
    $paymentBadge = [0 => 'badge-warning', 1 => 'badge-success', 2 => 'badge-danger', 3 => 'badge-dark'];
    $deliveryLabels = [0 => 'Not started', 1 => 'Processing', 2 => 'Shipped', 3 => 'Delivered', 4 => 'Failed'];
    $deliveryBadge = [0 => 'badge-secondary', 1 => 'badge-info', 2 => 'badge-primary', 3 => 'badge-success', 4 => 'badge-danger'];

    $customerName = trim((optional($data->User)->first_name ?? '') . ' ' . (optional($data->User)->last_name ?? ''));
    $itemsSubtotal = $data->items->sum(fn ($item) => (float) $item->subtotal);
    $tax = (float) ($data->tax_amount ?? 0);
    $discount = (float) ($data->discount_amount ?? 0);
    $total = (float) ($data->total_amount ?? 0);
    $address = $data->deliveryAddress;
    $phone = optional($data->User)->mobile_number
        ? trim((optional($data->User)->country_code ?? '') . ' ' . $data->User->mobile_number)
        : null;
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <x-admin.flash />

            <div class="cc-od">
                <div class="cc-od__toolbar">
                    <div class="cc-od__toolbar-left">
                        <a href="{{ route('get.Order') }}" class="cc-od__back" title="Back">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <div>
                            <h1>{{ $data->unique_id ?? ('#' . $data->id) }}</h1>
                            <span>{{ $data->created_at ? $data->created_at->format('d M Y, g:i A') : '—' }}</span>
                        </div>
                    </div>
                    <div class="cc-od__toolbar-right">
                        <span class="badge {{ $paymentBadge[$data->status] ?? 'badge-secondary' }}">
                            {{ $paymentLabels[$data->status] ?? 'Unknown' }}
                        </span>
                        <span class="badge {{ $deliveryBadge[$data->delivery_status] ?? 'badge-secondary' }}">
                            {{ $deliveryLabels[$data->delivery_status] ?? 'Unknown' }}
                        </span>
                    </div>
                </div>

                <div class="cc-od__meta">
                    <div>
                        <h3>Customer</h3>
                        <p>
                            <strong>{{ $customerName !== '' ? $customerName : '—' }}</strong>
                            @if(optional($data->User)->email)<br>{{ $data->User->email }}@endif
                            @if($phone)<br>{{ $phone }}@endif
                        </p>
                        @if($data->User)
                            <a href="{{ route('parent.show', encrypt($data->User->id)) }}">View profile</a>
                        @endif
                    </div>
                    <div>
                        <h3>Ship to</h3>
                        @if($address)
                            <p>
                                <strong>{{ $address->name ?? '—' }}</strong>
                                @if($address->address_type)
                                    <span class="cc-od__tag">{{ ucfirst($address->address_type) }}</span>
                                @endif
                                <br>{{ $address->address ?? '—' }}
                            </p>
                        @else
                            <p class="text-muted mb-0">No address</p>
                        @endif
                    </div>
                    <div>
                        <h3>Payment refs</h3>
                        <p class="cc-od__refs">
                            <span>Txn</span> <code>{{ $data->transaction_id ?: '—' }}</code><br>
                            <span>Pay</span> <code>{{ $data->payment_id ?: '—' }}</code>
                        </p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table cc-od__table mb-0">
                        <thead>
                            <tr>
                                <th style="width:40px">#</th>
                                <th>Product</th>
                                <th class="text-right">Price</th>
                                <th class="text-center">Qty</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->items as $key => $item)
                                @php $thumb = optional($item->product)->image; @endphp
                                <tr>
                                    <td class="text-muted">{{ $key + 1 }}</td>
                                    <td>
                                        <div class="cc-od__product">
                                            @if($thumb)
                                                <img src="{{ asset($thumb) }}" alt="">
                                            @else
                                                <span class="cc-od__product-ph"><i class="fas fa-box"></i></span>
                                            @endif
                                            <span>{{ $item->product_name }}</span>
                                        </div>
                                    </td>
                                    <td class="text-right">${{ number_format((float) $item->price, 2) }}</td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-right font-weight-bold">${{ number_format((float) $item->subtotal, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-3">No line items</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="cc-od__footer">
                    <form class="cc-od__status" action="{{ route('order.status.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $data->id }}">
                        <label for="delivery_status">Delivery status</label>
                        <div class="cc-od__status-row">
                            <select name="delivery_status" id="delivery_status" class="form-control form-control-sm">
                                <option value="0" {{ $data->delivery_status == 0 ? 'selected' : '' }}>Not started</option>
                                <option value="1" {{ $data->delivery_status == 1 ? 'selected' : '' }}>Processing</option>
                                <option value="2" {{ $data->delivery_status == 2 ? 'selected' : '' }}>Shipped</option>
                                <option value="3" {{ $data->delivery_status == 3 ? 'selected' : '' }}>Delivered</option>
                                <option value="4" {{ $data->delivery_status == 4 ? 'selected' : '' }}>Failed</option>
                            </select>
                            <button type="submit" class="btn btn-primary btn-sm">Update</button>
                        </div>
                    </form>

                    <table class="cc-od__totals">
                        <tr>
                            <td>Subtotal</td>
                            <td>${{ number_format($itemsSubtotal, 2) }}</td>
                        </tr>
                        @if($discount > 0)
                        <tr>
                            <td>Discount</td>
                            <td>−${{ number_format($discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Tax</td>
                            <td>${{ number_format($tax, 2) }}</td>
                        </tr>
                        <tr class="cc-od__totals-grand">
                            <td>Total</td>
                            <td>${{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
