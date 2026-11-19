@extends('admin.layouts.master')
@section('content')
@php
    $kidName = $childDetail->name ?? 'Kid';
    $recipesCount = $childDetail->recipes->count();
    $storiesCount = $childDetail->stories->count();
    $subsCount = $subscriptions->count();
    $activeSub = $subscriptions->first(fn ($s) => (int) $s->is_active === 1 && (int) $s->payment_status === 1);
    $parentName = $childDetail->parent
        ? trim(($childDetail->parent->first_name ?? '') . ' ' . ($childDetail->parent->last_name ?? ''))
        : '';
    $parentName = $parentName !== '' ? $parentName : '—';
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
                        <a href="{{ route('kid.index') }}" class="cc-ud__back" title="Back"><i class="fas fa-arrow-left"></i></a>
                        <img class="cc-ud__avatar" src="{{ asset($childDetail->image) }}" alt="{{ $kidName }}"
                             onerror="this.onerror=null;this.src='{{ asset('uploads/placeholder/default_user.png') }}'">
                        <div>
                            <h1>{{ $kidName }}</h1>
                            <span>Parent: {{ $parentName }}@if($childDetail->username) · {{ '@' . $childDetail->username }}@endif</span>
                        </div>
                    </div>
                    <div class="cc-ud__toolbar-right">
                        <span class="badge badge-secondary">{{ ucfirst($childDetail->role ?? 'kid') }}</span>
                        @if($childDetail->parent)
                            <a href="{{ route('parent.show', encrypt($childDetail->parent->id)) }}" class="btn btn-default btn-sm border">Parent</a>
                        @endif
                        <a href="{{ route('kid.edit', encrypt($childDetail->id)) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                    </div>
                </div>

                <div class="cc-ud__stats">
                    <div><strong>{{ $subsCount }}</strong><span>Subscriptions</span></div>
                    <div><strong>{{ $recipesCount }}</strong><span>Recipes</span></div>
                    <div><strong>{{ $storiesCount }}</strong><span>Stories</span></div>
                    <div><strong>{{ $childDetail->grade ?? '—' }}</strong><span>Grade</span></div>
                </div>

                <ul class="nav nav-tabs cc-ud__tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#ud-profile" role="tab">Profile</a>
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
                </ul>

                <div class="tab-content cc-ud__body">
                    <div class="tab-pane fade show active" id="ud-profile" role="tabpanel">
                        <table class="cc-ud__kv">
                            <tr><th>Name</th><td>{{ $kidName }}</td></tr>
                            <tr>
                                <th>Parent</th>
                                <td>
                                    @if($childDetail->parent)
                                        <a href="{{ route('parent.show', encrypt($childDetail->parent->id)) }}">{{ $parentName }}</a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                            <tr><th>Username</th><td>{{ $childDetail->username ?? '—' }}</td></tr>
                            <tr><th>Role</th><td>{{ $childDetail->role ?? '—' }}</td></tr>
                            <tr><th>Date of birth</th><td>{{ $childDetail->day ?? '—' }} / {{ $childDetail->month ?? '—' }} / {{ $childDetail->year ?? '—' }}</td></tr>
                            <tr><th>Grade</th><td>{{ $childDetail->grade ?? '—' }}</td></tr>
                            <tr>
                                <th>Active plan</th>
                                <td>
                                    @if($activeSub)
                                        {{ optional($activeSub->subscriptionPlan)->name ?? '—' }}
                                        <span class="badge badge-success ml-1">Active</span>
                                    @else
                                        <span class="text-muted">None</span>
                                    @endif
                                </td>
                            </tr>
                            <tr><th>Created</th><td>{{ $childDetail->created_at ? $childDetail->created_at->format('d M Y, g:i A') : '—' }}</td></tr>
                        </table>
                    </div>

                    <div class="tab-pane fade" id="ud-subs" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table cc-ud__table mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Plan</th>
                                        <th class="text-right">Amount</th>
                                        <th>Paid by</th>
                                        <th>Payment</th>
                                        <th>Status</th>
                                        <th>Start</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($subscriptions as $purchase)
                                        @php
                                            $buyer = $purchase->user
                                                ? trim(($purchase->user->first_name ?? '') . ' ' . ($purchase->user->last_name ?? ''))
                                                : '—';
                                        @endphp
                                        <tr>
                                            <td>{{ $purchase->created_at ? $purchase->created_at->format('d M Y') : '—' }}</td>
                                            <td>{{ optional($purchase->subscriptionPlan)->name ?? '—' }}</td>
                                            <td><strong>${{ number_format((float) (optional($purchase->subscriptionPlan)->amount ?? 0), 2) }}</strong></td>
                                            <td>
                                                @if($purchase->user)
                                                    <a href="{{ route('parent.show', encrypt($purchase->user->id)) }}">{{ $buyer !== '' ? $buyer : '—' }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
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
                                        <tr><td colspan="7" class="text-center text-muted py-3">No subscriptions for this kid</td></tr>
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
                                    @forelse($childDetail->recipes as $key => $value)
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
                                            </td>
                                            <td class="text-center">
                                                <div class="cc-actions">
                                                    <a href="{{ route('recipe.show', encrypt($value['id'])) }}" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="javascript:void(0)" record="recipe-delete" record_id="{{ encrypt($value['id']) }}" class="confirmDelete cc-action-delete" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No recipes</td></tr>
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
                                    @forelse($childDetail->stories as $key => $story)
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
                                            </td>
                                            <td class="text-center">
                                                <div class="cc-actions">
                                                    <a href="{{ route('story.show', encrypt($story['id'])) }}" title="View"><i class="fa-solid fa-eye"></i></a>
                                                    <a href="javascript:void(0)" record="story-delete" record_id="{{ encrypt($story['id']) }}" class="confirmDelete cc-action-delete" title="Delete"><i class="fa-solid fa-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center text-muted py-3">No stories</td></tr>
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
