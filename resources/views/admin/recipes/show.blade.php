@extends('admin.layouts.master')
@section('content')
@php
    $recipeName = $recipes->name ?? 'Recipe';
    $nAge = $recipes->recipeAgeGroup->count();
    $nTools = $recipes->recipeToolsList->count();
    $nIng = $recipes->recipeIngredient->count();
    $nCat = $recipes->recipeCategories->count();
    $nSteps = $recipes->recipeSteps->count();
    $addedBy = '—';
    if ($recipes->role === 'parent' && $recipes->recipeAddedByParent) {
        $addedBy = trim(($recipes->recipeAddedByParent->first_name ?? '') . ' ' . ($recipes->recipeAddedByParent->last_name ?? '')) ?: '—';
    } elseif ($recipes->role === 'kid' && $recipes->recipeAddedByChild) {
        $addedBy = $recipes->recipeAddedByChild->name ?? '—';
    } elseif ($recipes->role === 'admin' || $recipes->role === null) {
        $addedBy = 'Admin';
    }
@endphp
<div class="content-wrapper admin-detail-page">
        <x-admin.page-header :title="$recipeName" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <x-admin.flash />
@if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header p-0 border-bottom-0">
                    <ul class="nav nav-tabs px-2 pt-2 flex-wrap" role="tablist">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab_basic" role="tab">Overview</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_age" role="tab">Age groups ({{ $nAge }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_tools" role="tab">Tools ({{ $nTools }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_ing" role="tab">Ingredients ({{ $nIng }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_cat" role="tab">Categories ({{ $nCat }})</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab_steps" role="tab">Steps ({{ $nSteps }})</a></li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab_basic" role="tabpanel">
                            <div class="row">
                                <div class="col-12 col-md-4 text-center text-md-left mb-3 mb-md-0">
                                    <img src="{{ asset($recipes->banner_image) }}" alt="" class="detail-avatar img-fluid">
                                    <div class="mt-3">
                                        <a href="{{ route('recipe.edit', encrypt($recipes->id)) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen mr-1"></i> Edit</a>
                                        <a href="{{ route('recipe.index') }}" class="btn btn-default btn-sm border"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                                    </div>
                                </div>
                                <div class="col-12 col-md-8">
                                    <dl class="row dl-detail">
                                        <dt class="col-sm-4">Recipe name</dt><dd class="col-sm-8">{{ $recipeName }}</dd>
                                        <dt class="col-sm-4">Added by</dt><dd class="col-sm-8">{{ $addedBy }}</dd>
                                        <dt class="col-sm-4">Role</dt><dd class="col-sm-8"><span class="badge badge-secondary">{{ $recipes->role ?? '—' }}</span></dd>
                                        <dt class="col-sm-4">Popular</dt><dd class="col-sm-8">{{ $recipes->status == 0 ? 'No' : 'Yes' }}</dd>
                                        <dt class="col-sm-4">Recipe of the week</dt><dd class="col-sm-8">{{ $recipes->recipe_of_the_week == 0 ? 'No' : 'Yes' }}</dd>
                                        <dt class="col-sm-4">Video URL</dt><dd class="col-sm-8 text-break"><small>{{ $recipes->video_link ?: '—' }}</small></dd>
                                        @if($recipes->created_at)
                                            <dt class="col-sm-4">Created</dt><dd class="col-sm-8">{{ $recipes->created_at->format('M j, Y g:i A') }}</dd>
                                        @endif
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab_age" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover mb-0">
                                    <thead class="thead-light"><tr><th style="width:60px">#</th><th>Age range</th></tr></thead>
                                    <tbody>
                                        @forelse($recipes->recipeAgeGroup as $key => $ageGrp)
                                            <tr>
                                                <td class="text-center text-muted">{{ $key + 1 }}</td>
                                                <td>{{ $ageGrp->ageGroup ? ($ageGrp->ageGroup->age_from . ' – ' . $ageGrp->ageGroup->age_to) : '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-4">No age groups.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab_tools" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover mb-0">
                                    <thead class="thead-light"><tr><th style="width:60px">#</th><th style="width:80px">Image</th><th>Name</th></tr></thead>
                                    <tbody>
                                        @forelse($recipes->recipeToolsList as $key => $tool)
                                            <tr>
                                                <td class="text-center text-muted">{{ $key + 1 }}</td>
                                                <td><img src="{{ asset($tool->tool_image) }}" width="48" height="48" class="rounded" style="object-fit:cover" alt=""></td>
                                                <td>{{ $tool->tool_name ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center text-muted py-4">No tools.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab_ing" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover mb-0">
                                    <thead class="thead-light"><tr><th style="width:60px">#</th><th>Ingredient</th></tr></thead>
                                    <tbody>
                                        @forelse($recipes->recipeIngredient as $key => $recipeIng)
                                            <tr><td class="text-center text-muted">{{ $key + 1 }}</td><td>{{ $recipeIng->name ?? '—' }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-4">No ingredients.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab_cat" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover mb-0">
                                    <thead class="thead-light"><tr><th style="width:60px">#</th><th>Category</th></tr></thead>
                                    <tbody>
                                        @forelse($recipes->recipeCategories as $key => $recipecat)
                                            <tr><td class="text-center text-muted">{{ $key + 1 }}</td><td>{{ $recipecat->category->name ?? '—' }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-4">No categories.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab_steps" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm table-hover mb-0">
                                    <thead class="thead-light"><tr><th style="width:60px">#</th><th>Step</th></tr></thead>
                                    <tbody>
                                        @forelse($recipes->recipeSteps as $key => $recipeStep)
                                            <tr><td class="text-center text-muted">{{ $key + 1 }}</td><td>{{ $recipeStep->name ?? '—' }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-center text-muted py-4">No steps.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('script')
<script>$(function () { $('.alert-dismissible').fadeOut(4000); });</script>
@endpush
