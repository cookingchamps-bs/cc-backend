@extends('admin.layouts.master')
@section('content')
@php
    $panel = 'admin';
@endphp
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Vendor'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <x-admin.flash />
            <x-admin.data-card title="Vendor information" class="mb-4">
                    <dl class="row mb-0">
                        <dt class="col-sm-3 col-md-2">Username</dt>
                        <dd class="col-sm-9 col-md-4">{{ $vendor->username ?? '—' }}</dd>
                        <dt class="col-sm-3 col-md-2">Name</dt>
                        <dd class="col-sm-9 col-md-4">{{ $vendor->name ?? '—' }}</dd>
                        <dt class="col-sm-3 col-md-2">Mobile number</dt>
                        <dd class="col-sm-9 col-md-4">{{ $vendor->mobile_no ?? '—' }}</dd>
                        <dt class="col-sm-3 col-md-2">Company name</dt>
                        <dd class="col-sm-9 col-md-4">{{ $vendor->company_name ?? '—' }}</dd>
                        <dt class="col-sm-3 col-md-2">Registered</dt>
                        <dd class="col-sm-9 col-md-4">{{ $vendor->created_at ? $vendor->created_at->format('d M Y, g:i A') : '—' }}</dd>
                    </dl>
            </x-admin.data-card>

            <div class="card card-outline card-primary">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs px-2 pt-2 flex-wrap" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#tab-products" role="tab">
                                <i class="fa fa-box mr-1"></i> Products
                                <span class="badge badge-secondary ml-1">{{ $vendor->products->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-orders" role="tab">
                                <i class="fa fa-shopping-cart mr-1"></i> Orders
                                <span class="badge badge-secondary ml-1">{{ $orders->count() }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#tab-revenue" role="tab">
                                <i class="fa fa-chart-line mr-1"></i> Revenue
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-0">
                    <div class="tab-content">
                        <div id="tab-products" class="tab-pane fade show active" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:56px">#</th>
                                            <th class="text-center" style="width:64px">Image</th>
                                            <th>Title</th>
                                            <th>Category</th>
                                            <th class="text-right">Selling price</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($vendor->products as $key => $product)
                                            <tr>
                                                <td class="text-center text-muted">{{ $key + 1 }}</td>
                                                <td>
                                                    @if(!empty($product->image))
                                                        <img class="tbl-img-css rounded" width="56" height="56" style="object-fit: cover;" src="{{ asset($product->image) }}" alt="">
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $product->title }}</td>
                                                <td>{{ $product->productCategory->name ?? '—' }}</td>
                                                <td class="text-right">${{ number_format((float) $product->selling_price, 2) }}</td>
                                                <td class="text-center">{{ $product->quantity }}</td>
                                                <td class="text-center">
                                                    @if($product->status == 1)
                                                        <span class="badge badge-success">Active</span>
                                                    @else
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <a href="{{ route($panel . '.products.edit', encrypt($product->id)) }}" title="Edit product">
                                                        <i class="fa-solid fa-pencil"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">No products added by this vendor yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="tab-orders" class="tab-pane fade" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width:56px">#</th>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Vendor line items</th>
                                            <th class="text-right">Vendor subtotal</th>
                                            <th class="text-right">Order total</th>
                                            <th class="text-center">Payment</th>
                                            <th class="text-center">Delivery</th>
                                            <th>Date</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($orders as $key => $order)
                                            @php
                                                $vendorSubtotal = $order->items->sum(fn ($item) => (float) $item->subtotal);
                                            @endphp
                                            <tr>
                                                <td class="text-center text-muted">{{ $key + 1 }}</td>
                                                <td>{{ $order->unique_id ?? $order->id }}</td>
                                                <td>
                                                    @php $cust = $order->user; @endphp
                                                    {{ $cust ? trim(($cust->first_name ?? '') . ' ' . ($cust->last_name ?? '')) ?: '—' : '—' }}
                                                </td>
                                                <td>
                                                    @foreach($order->items as $item)
                                                        <div class="small">{{ $item->product_name }} &times; {{ $item->quantity }}</div>
                                                    @endforeach
                                                </td>
                                                <td class="text-right">${{ number_format($vendorSubtotal, 2) }}</td>
                                                <td class="text-right">${{ number_format((float) ($order->total_amount ?? 0), 2) }}</td>
                                                <td class="text-center">
                                                    @if($order->status == 0)
                                                        <span class="badge badge-warning">Pending</span>
                                                    @elseif($order->status == 1)
                                                        <span class="badge badge-success">Paid</span>
                                                    @elseif($order->status == 2)
                                                        <span class="badge badge-danger">Failed</span>
                                                    @elseif($order->status == 3)
                                                        <span class="badge badge-dark">Cancelled</span>
                                                    @else
                                                        <span class="badge badge-secondary">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($order->delivery_status == 0)
                                                        <span class="badge badge-secondary">Not started</span>
                                                    @elseif($order->delivery_status == 1)
                                                        <span class="badge badge-info">Processing</span>
                                                    @elseif($order->delivery_status == 2)
                                                        <span class="badge badge-primary">Shipped</span>
                                                    @elseif($order->delivery_status == 3)
                                                        <span class="badge badge-success">Delivered</span>
                                                    @elseif($order->delivery_status == 4)
                                                        <span class="badge badge-danger">Failed</span>
                                                    @else
                                                        <span class="badge badge-secondary">—</span>
                                                    @endif
                                                </td>
                                                <td>{{ $order->created_at ? $order->created_at->format('d M Y') : '—' }}</td>
                                                <td class="text-center">
                                                    <a href="{{ route('view.order', $order->id) }}" class="btn btn-sm btn-info" title="View order">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="10" class="text-center text-muted py-4">No orders yet for this vendor&rsquo;s products.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="tab-revenue" class="tab-pane fade p-3" role="tabpanel">
                            <p class="text-muted small mb-3">
                                Amounts are the sum of this vendor&rsquo;s line-item subtotals only (not the full order total when the cart includes other vendors).
                                <strong>Paid revenue</strong> counts orders with payment status <em>Paid</em>.
                            </p>
                            <div class="row">
                                <div class="col-sm-6 col-lg-3 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="text-muted text-uppercase small font-weight-bold">Paid revenue</div>
                                        <div class="h4 mb-0 text-success">${{ number_format($revenue['paid_total'], 2) }}</div>
                                        <div class="small text-muted mt-1">From paid orders</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="text-muted text-uppercase small font-weight-bold">Pending revenue</div>
                                        <div class="h4 mb-0 text-warning">${{ number_format($revenue['pending_total'], 2) }}</div>
                                        <div class="small text-muted mt-1">Unpaid orders only</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="text-muted text-uppercase small font-weight-bold">Paid orders</div>
                                        <div class="h4 mb-0">{{ number_format($revenue['paid_orders_count']) }}</div>
                                        <div class="small text-muted mt-1">Orders with status Paid</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-lg-3 mb-3">
                                    <div class="border rounded p-3 h-100 bg-light">
                                        <div class="text-muted text-uppercase small font-weight-bold">Units sold (paid)</div>
                                        <div class="h4 mb-0">{{ number_format($revenue['units_sold_paid']) }}</div>
                                        <div class="small text-muted mt-1">Quantity on paid orders</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
