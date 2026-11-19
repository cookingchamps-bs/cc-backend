@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$category->name ?? ($common['title'] ?? 'Category')" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <x-admin.data-card :title="$common['title'] ?? 'Category'">
                <div class="mb-3 text-right">
                    <a href="{{ route('categories.edit', encrypt($category->id)) }}" class="btn btn-primary btn-sm"><i class="fas fa-pen mr-1"></i> Edit</a>
                    <a href="{{ route('categories.index') }}" class="btn btn-default btn-sm border"><i class="fas fa-arrow-left mr-1"></i> Back</a>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4 text-center text-md-left mb-3 mb-md-0">
                        <img src="{{ asset($category->image) }}" alt="" class="detail-avatar img-fluid" style="max-width: 220px;">
                    </div>
                    <div class="col-12 col-md-8">
                        <dl class="row dl-detail">
                            <dt class="col-sm-4">Name</dt><dd class="col-sm-8">{{ $category->name ?? '—' }}</dd>
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                @if(($category->status ?? 0) == 1)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </dd>
                            @if($category->created_at)
                                <dt class="col-sm-4">Created</dt><dd class="col-sm-8">{{ $category->created_at->format('M j, Y g:i A') }}</dd>
                            @endif
                        </dl>
                    </div>
                </div>
            </x-admin.data-card>
        </div>
    </section>
</div>
@endsection
@push('script')
<script>$(function () { $('.alert-dismissible').fadeOut(4000); });</script>
@endpush
