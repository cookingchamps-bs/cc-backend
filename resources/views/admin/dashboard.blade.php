@extends('admin.layouts.master')
@section('content')
@php
    $o = $dashboard['overview'];
    $shop = $dashboard['shop_revenue'];
    $sub = $dashboard['subscription_revenue'];
    $fmt = fn ($n) => number_format((float) $n, 2);
    $adminName = Auth::guard('admin')->check() ? Auth::guard('admin')->user()->name : 'Admin';
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            <x-admin.flash />

            {{-- Welcome + primary revenue --}}
            <div class="cc-dash-hero mb-4">
                <div class="cc-dash-hero__copy">
                    <p class="cc-dash-hero__eyebrow">Overview</p>
                    <h1 class="cc-dash-hero__title">Hello, {{ $adminName }}</h1>
                    <p class="cc-dash-hero__sub">Shop and subscription performance at a glance.</p>
                </div>
                <div class="cc-dash-hero__metrics">
                    <div class="cc-dash-hero__metric">
                        <span class="cc-dash-hero__metric-label">Shop today</span>
                        <span class="cc-dash-hero__metric-value">${{ $fmt($shop['today']['gross']) }}</span>
                        <span class="cc-dash-hero__metric-meta">{{ $shop['today']['count'] }} orders · tax ${{ $fmt($shop['today']['tax']) }}</span>
                    </div>
                    <div class="cc-dash-hero__metric cc-dash-hero__metric--accent">
                        <span class="cc-dash-hero__metric-label">Subscriptions today</span>
                        <span class="cc-dash-hero__metric-value">${{ $fmt($sub['today']['total']) }}</span>
                        <span class="cc-dash-hero__metric-meta">{{ $sub['today']['count'] }} purchases</span>
                    </div>
                </div>
            </div>

            {{-- Period revenue: two columns, not six twin cards --}}
            <div class="row mb-4">
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="cc-dash-panel">
                        <div class="cc-dash-panel__head">
                            <h2>Shop revenue</h2>
                            <a href="{{ route('get.Order') }}">View orders</a>
                        </div>
                        <div class="cc-dash-period">
                            <div>
                                <span class="cc-dash-period__label">This week</span>
                                <strong>${{ $fmt($shop['week']['gross']) }}</strong>
                                <span>{{ $shop['week']['count'] }} orders</span>
                            </div>
                            <div>
                                <span class="cc-dash-period__label">This month</span>
                                <strong>${{ $fmt($shop['month']['gross']) }}</strong>
                                <span>{{ $shop['month']['count'] }} orders</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="cc-dash-panel">
                        <div class="cc-dash-panel__head">
                            <h2>Subscription revenue</h2>
                            <a href="{{ route('subscription.purchases') }}">View purchases</a>
                        </div>
                        <div class="cc-dash-period">
                            <div>
                                <span class="cc-dash-period__label">This week</span>
                                <strong>${{ $fmt($sub['week']['total']) }}</strong>
                                <span>{{ $sub['week']['count'] }} purchases</span>
                            </div>
                            <div>
                                <span class="cc-dash-period__label">This month</span>
                                <strong>${{ $fmt($sub['month']['total']) }}</strong>
                                <span>{{ $sub['month']['count'] }} purchases</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Compact overview strip --}}
            <div class="cc-dash-strip mb-4">
                <a href="{{ route('parent.index') }}" class="cc-dash-strip__item">
                    <span class="cc-dash-strip__n">{{ $o['parent_count'] }}</span>
                    <span class="cc-dash-strip__l">Parents</span>
                </a>
                <a href="{{ route('kid.index') }}" class="cc-dash-strip__item">
                    <span class="cc-dash-strip__n">{{ $o['kid_count'] }}</span>
                    <span class="cc-dash-strip__l">Kids</span>
                </a>
                <a href="{{ route('story.index') }}" class="cc-dash-strip__item">
                    <span class="cc-dash-strip__n">{{ $o['story_count'] }}</span>
                    <span class="cc-dash-strip__l">Stories</span>
                </a>
                <a href="{{ route('recipe.index') }}" class="cc-dash-strip__item">
                    <span class="cc-dash-strip__n">{{ $o['recipe_count'] }}</span>
                    <span class="cc-dash-strip__l">Recipes</span>
                </a>
                <div class="cc-dash-strip__item">
                    <span class="cc-dash-strip__n">{{ $o['active_subscriptions'] }}</span>
                    <span class="cc-dash-strip__l">Active plans</span>
                </div>
                <a href="{{ route('get.Order') }}" class="cc-dash-strip__item {{ (int)$o['pending_orders'] > 0 ? 'is-alert' : '' }}">
                    <span class="cc-dash-strip__n">{{ $o['pending_orders'] }}</span>
                    <span class="cc-dash-strip__l">Pending orders</span>
                </a>
                <div class="cc-dash-strip__item">
                    <span class="cc-dash-strip__n">{{ $o['paid_orders_all_time'] }}</span>
                    <span class="cc-dash-strip__l">Paid orders</span>
                </div>
            </div>

            {{-- Activity: lighter list panels --}}
            <div class="row">
                <div class="col-lg-4 mb-3">
                    <div class="cc-dash-panel cc-dash-panel--list">
                        <div class="cc-dash-panel__head">
                            <h2>Latest stories</h2>
                            <a href="{{ route('story.index') }}">All</a>
                        </div>
                        <ul class="cc-dash-list">
                            @forelse($dashboard['latest_stories'] as $story)
                                <li>
                                    <div class="cc-dash-list__main">
                                        <strong title="{{ $story->title }}">{{ Str::limit($story->title, 32) }}</strong>
                                        <span>
                                            @if($story->storyAddedByParent)
                                                {{ Str::limit($story->storyAddedByParent->name, 18) }}
                                            @elseif($story->role === 'admin')
                                                Admin
                                            @else
                                                —
                                            @endif
                                            · {{ $story->created_at?->format('M j') }}
                                        </span>
                                    </div>
                                    <a class="cc-dash-list__action" href="{{ route('story.edit', encrypt($story->id)) }}" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                </li>
                            @empty
                                <li class="cc-dash-list__empty">No stories yet</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="cc-dash-panel cc-dash-panel--list">
                        <div class="cc-dash-panel__head">
                            <h2>Recent paid orders</h2>
                            <a href="{{ route('get.Order') }}">All</a>
                        </div>
                        <ul class="cc-dash-list">
                            @forelse($dashboard['recent_orders'] as $order)
                                <li>
                                    <div class="cc-dash-list__main">
                                        <strong>{{ $order->unique_id ?? '#' . $order->id }}</strong>
                                        <span>
                                            {{ $order->User ? Str::limit($order->User->name, 20) : '—' }}
                                            · ${{ $fmt($order->total_amount) }}
                                        </span>
                                    </div>
                                    <a class="cc-dash-list__action" href="{{ route('view.order', $order->id) }}" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </li>
                            @empty
                                <li class="cc-dash-list__empty">No paid orders yet</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-lg-4 mb-3">
                    <div class="cc-dash-panel cc-dash-panel--list">
                        <div class="cc-dash-panel__head">
                            <h2>Recent subscriptions</h2>
                            <a href="{{ route('subscription.purchases') }}">All</a>
                        </div>
                        <ul class="cc-dash-list">
                            @forelse($dashboard['recent_subscriptions'] as $us)
                                @php
                                    $buyer = $us->user
                                        ? trim(($us->user->first_name ?? '') . ' ' . ($us->user->last_name ?? ''))
                                        : '';
                                    $buyer = $buyer !== '' ? $buyer : '—';
                                    $forKid = $us->kid?->name;
                                @endphp
                                <li>
                                    <div class="cc-dash-list__main">
                                        <strong>{{ Str::limit($buyer, 22) }}@if($forKid) → {{ Str::limit($forKid, 14) }}@endif</strong>
                                        <span>
                                            {{ Str::limit(optional($us->subscriptionPlan)->name ?? '—', 22) }}
                                            · ${{ $fmt(optional($us->subscriptionPlan)->amount ?? 0) }}
                                            · {{ $us->created_at?->format('M j') }}
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="cc-dash-list__empty">No subscription purchases yet</li>
                            @endforelse
                        </ul>
                    </div>
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
