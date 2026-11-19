@extends('admin.layouts.master')
@section('content')
@php
    $fullName = trim(($parentDetail->first_name ?? '') . ' ' . ($parentDetail->last_name ?? ''));
    $fullName = $fullName !== '' ? $fullName : 'Parent';
    $kidsCount = $parentDetail->kids->count();
    $recipesCount = $parentDetail->recipes->count();
    $storiesCount = $parentDetail->stories->count();
    $ordersCount = $parentDetail->orders->count();
    $subsCount = $parentDetail->subscriptionPurchases->count();
    $phone = $parentDetail->mobile_number
        ? trim(($parentDetail->country_code ?? '') . ' ' . $parentDetail->mobile_number)
        : null;
    $paymentLabels = [0 => 'Pending', 1 => 'Paid', 2 => 'Failed', 3 => 'Cancelled'];
    $paymentBadge = [0 => 'badge-warning', 1 => 'badge-success', 2 => 'badge-danger', 3 => 'badge-dark'];
    $subPayLabels = [0 => 'Unpaid', 1 => 'Paid'];
    $subPayBadge = [0 => 'badge-warning', 1 => 'badge-success'];
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <x-admin.flash />
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            <div class="cc-ud">
                <div class="cc-ud__toolbar">
                    <div class="cc-ud__toolbar-left">
                        <a href="{{ route('parent.index') }}" class="cc-ud__back" title="Back"><i class="fas fa-arrow-left"></i></a>
                        <img class="cc-ud__avatar" src="{{ asset($parentDetail->image) }}" alt="{{ $fullName }}"
                             onerror="this.onerror=null;this.src='{{ asset('uploads/placeholder/default_user.png') }}'">
                        <div>
                            <h1>{{ $fullName }}</h1>
                            <span>
                                {{ $parentDetail->email ?? '—' }}
                                @if($phone) · {{ $phone }}@endif
                            </span>
                        </div>
                    </div>
                    <div class="cc-ud__toolbar-right">
                        <span class="badge badge-info">{{ ucfirst($parentDetail->role ?? 'parent') }}</span>
                        <a href="{{ route('parent.edit', encrypt($parentDetail->id)) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="cc-ud__stats">
                    <div><strong>{{ $kidsCount }}</strong><span>Kids</span></div>
                    <div><strong>{{ $subsCount }}</strong><span>Subscriptions</span></div>
                    <div><strong>{{ $storiesCount }}</strong><span>Stories</span></div>
                    <div><strong>{{ $ordersCount }}</strong><span>Orders</span></div>
                </div>

                <ul class="nav nav-tabs cc-ud__tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#ud-profile" role="tab">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#ud-kids" role="tab">Kids ({{ $kidsCount }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#ud-subs" role="tab">Subscriptions ({{ $subsCount }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#ud-recipes" role="tab">Recipes ({{ $recipesCount }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#ud-stories" role="tab">Stories ({{ $storiesCount }})</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#ud-orders" role="tab">Orders ({{ $ordersCount }})</a>
                    </li>
                </ul>

                <div class="tab-content cc-ud__body">
                    <div class="tab-pane fade show active" id="ud-profile" role="tabpanel">
                        <table class="cc-ud__kv">
                            <tr><th>Full name</th><td>{{ $fullName }}</td></tr>
                            <tr><th>Email</th><td>{{ $parentDetail->email ?? '—' }}</td></tr>
                            <tr><th>Phone</th><td>{{ $phone ?: '—' }}</td></tr>
                            <tr><th>Role</th><td>{{ $parentDetail->role ?? '—' }}@if($parentDetail->sub_role) / {{ $parentDetail->sub_role }}@endif</td></tr>
                            <tr><th>Login type</th><td>{{ $parentDetail->login_type ?? '—' }}</td></tr>
                            <tr><th>Date of birth</th><td>{{ $parentDetail->dob ?? '—' }}</td></tr>
                            <tr><th>Member since</th><td>{{ $parentDetail->created_at ? $parentDetail->created_at->format('d M Y, g:i A') : '—' }}</td></tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="ud-kids" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table cc-ud__table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Kid</th>
                                        <th>DOB</th>
                                        <th>Grade</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parentDetail->kids as $key => $kid)
                                        <tr>
                                            <td class="text-muted">{{ $key + 1 }}</td>
                                            <td>
                                                <div class="cc-ud__row-user">
                                                    <img src="{{ asset($kid->image) }}" alt=""
                                                         onerror="this.onerror=null;this.src='{{ asset('uploads/placeholder/default_user.png') }}'">
                                                    <div>
                                                        <strong>{{ $kid->name }}</strong>
                                                        <small>{{ $kid->role }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $kid->day }}/{{ $kid->month }}/{{ $kid->year }}</td>
                                            <td>{{ $kid->grade ?? '—' }}</td>
                                            <td class="text-center">
                                                <div class="cc-actions">
                                                    <a href="{{ route('kid.show', encrypt($kid->id)) }}" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="{{ route('kid.edit', encrypt($kid->id)) }}" title="Edit"><i class="fa-solid fa-pencil"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted py-3">No kids linked</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="ud-subs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table cc-ud__table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Kid</th>
                                        <th>Plan</th>
                                        <th class="text-right">Amount</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Start</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parentDetail->subscriptionPurchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->created_at ? $purchase->created_at->format('d M Y') : '—' }}</td>
                                            <td>
                                                @if($purchase->kid)
                                                    <a href="{{ route('kid.show', encrypt($purchase->kid->id)) }}">{{ $purchase->kid->name }}</a>
                                                @else
                                                    <span class="text-muted">Parent-level</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($purchase->subscriptionPlan)->name ?? '—' }}</td>
                                            <td><strong>${{ number_format((float) (optional($purchase->subscriptionPlan)->amount ?? 0), 2) }}</strong></td>
                                            <td>
                                                <span class="badge {{ $subPayBadge[(int) $purchase->payment_status] ?? 'badge-secondary' }}">
                                                    {{ $subPayLabels[(int) $purchase->payment_status] ?? '—' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($purchase->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $purchase->start_date ? \Carbon\Carbon::parse($purchase->start_date)->format('d M Y') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="7" class="text-center text-muted py-3">No subscription purchases</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="ud-recipes" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table cc-ud__table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Recipe</th>
                                        <th>Added by</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parentDetail->recipes as $key => $value)
                                        <tr>
                                            <td class="text-muted">{{ $key + 1 }}</td>
                                            <td>
                                                <div class="cc-ud__row-user">
                                                    <img src="{{ $value['banner_image'] != '' ? asset($value['banner_image']) : asset('uploads/placeholder/default_user.png') }}" alt="">
                                                    <strong>{{ $value['name'] }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                @if($value->role === 'parent')
                                                    {{ trim(($value->recipeAddedByParent->first_name ?? '') . ' ' . ($value->recipeAddedByParent->last_name ?? '')) ?: '—' }}
                                                @elseif($value->role === 'kid')
                                                    {{ $value->recipeAddedByChild->name ?? '—' }}
                                                @else
                                                    —
                                                @endif
                                                <small class="d-block text-muted">{{ $value->role }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="cc-actions">
                                                    <a href="{{ route('recipe.show', encrypt($value['id'])) }}" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="javascript:void(0)" record="recipe-delete" record_id="{{ encrypt($value['id']) }}" class="confirmDelete cc-action-delete" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No recipes yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="ud-stories" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table cc-ud__table mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Story</th>
                                        <th>Added by</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parentDetail->stories as $key => $story)
                                        @php $descPlain = strip_tags($story['description'] ?? ''); @endphp
                                        <tr>
                                            <td class="text-muted">{{ $key + 1 }}</td>
                                            <td>
                                                <div class="cc-ud__row-user">
                                                    <img src="{{ $story['image'] != '' ? asset($story['image']) : asset('uploads/placeholder/default_user.png') }}" alt="">
                                                    <div>
                                                        <strong>{{ $story['title'] }}</strong>
                                                        <small>{{ Str::limit($descPlain, 70) }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($story->role === 'parent')
                                                    {{ trim(($story->storyAddedByParent?->first_name ?? '') . ' ' . ($story->storyAddedByParent?->last_name ?? '')) ?: '—' }}
                                                @elseif($story->role === 'kid')
                                                    {{ $story->storyAddedByChild?->name ?? '—' }}
                                                @else
                                                    —
                                                @endif
                                                <small class="d-block text-muted">{{ $story->role }}</small>
                                            </td>
                                            <td class="text-center">
                                                <div class="cc-actions">
                                                    <a href="{{ route('story.show', encrypt($story['id'])) }}" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="javascript:void(0)" record="story-delete" record_id="{{ encrypt($story['id']) }}" class="confirmDelete cc-action-delete" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No stories yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tab-pane fade" id="ud-orders" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table cc-ud__table mb-0">
                                <thead>
                                    <tr>
                                        <th>Order</th>
                                        <th>Items</th>
                                        <th class="text-right">Total</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($parentDetail->orders as $order)
                                        <tr>
                                            <td>
                                                <a href="{{ route('view.order', $order->id) }}" class="cc-order-id">
                                                    {{ $order->unique_id ?? ('#' . $order->id) }}
                                                </a>
                                            </td>
                                            <td>{{ $order->items_count ?? '—' }}</td>
                                            <td><strong>${{ number_format((float) $order->total_amount, 2) }}</strong></td>
                                            <td>
                                                <span class="badge {{ $paymentBadge[$order->status] ?? 'badge-secondary' }}">
                                                    {{ $paymentLabels[$order->status] ?? '—' }}
                                                </span>
                                            </td>
                                            <td>{{ $order->created_at ? $order->created_at->format('d M Y') : '—' }}</td>
                                            <td class="text-center">
                                                <div class="cc-actions">
                                                    <a href="{{ route('view.order', $order->id) }}" title="View"><i class="fa-solid fa-eye"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted py-3">No orders yet</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <input type="hidden" id="rootUrl" value="{{ rtrim(config('app.url'), '/') }}">
        </div>
    </section>
</div>
@endsection
@push('script')
<script>
    $(function () { $('.alert-dismissible').fadeOut(4000); });
</script>
@endpush
