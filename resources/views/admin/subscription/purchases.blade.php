@extends('admin.layouts.master')
@section('content')
@php
    $payLabels = [0 => 'Unpaid', 1 => 'Paid'];
    $payBadge = [0 => 'badge-warning', 1 => 'badge-success'];
@endphp
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title']" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card title="Subscription purchases">
                        <div class="table-responsive">
                            <table id="subscription_purchases" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Date</th>
                                        <th>Parent</th>
                                        <th>Kid</th>
                                        <th>Plan</th>
                                        <th class="text-right">Amount</th>
                                        <th class="text-center">Payment</th>
                                        <th class="text-center">Active</th>
                                        <th>Start</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($purchases as $key => $purchase)
                                        @php
                                            $parentName = $purchase->user
                                                ? trim(($purchase->user->first_name ?? '') . ' ' . ($purchase->user->last_name ?? ''))
                                                : '';
                                            $parentName = $parentName !== '' ? $parentName : '—';
                                        @endphp
                                        <tr>
                                            <td class="text-center text-muted">{{ $key + 1 }}</td>
                                            <td>
                                                {{ $purchase->created_at ? $purchase->created_at->format('d M Y') : '—' }}
                                                @if($purchase->created_at)
                                                    <small class="d-block text-muted">{{ $purchase->created_at->format('g:i A') }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($purchase->user)
                                                    <a href="{{ route('parent.show', encrypt($purchase->user->id)) }}">{{ $parentName }}</a>
                                                    @if($purchase->user->email)
                                                        <small class="d-block text-muted">{{ $purchase->user->email }}</small>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>
                                                @if($purchase->kid)
                                                    <a href="{{ route('kid.show', encrypt($purchase->kid->id)) }}">{{ $purchase->kid->name }}</a>
                                                @else
                                                    <span class="text-muted">Parent-level</span>
                                                @endif
                                            </td>
                                            <td>{{ optional($purchase->subscriptionPlan)->name ?? '—' }}</td>
                                            <td class="text-right">
                                                <strong>${{ number_format((float) (optional($purchase->subscriptionPlan)->amount ?? 0), 2) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $payBadge[(int) $purchase->payment_status] ?? 'badge-secondary' }}">
                                                    {{ $payLabels[(int) $purchase->payment_status] ?? '—' }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @if($purchase->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td>{{ $purchase->start_date ? \Carbon\Carbon::parse($purchase->start_date)->format('d M Y') : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9">
                                                <x-admin.empty-state message="No subscription purchases yet" icon="fa-credit-card" />
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
