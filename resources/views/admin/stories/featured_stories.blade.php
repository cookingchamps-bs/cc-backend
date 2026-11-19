@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title']" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['title']">
                        <div class="table-responsive">
                            <table id="qualification" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th class="text-center" style="width:64px">Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Added By</th>
                                        <th>Role</th>
                                        <th>Featured Stories</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stories as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center"><img class="tbl-img-css" width="50px" height="50px"  style="border-radius: 50%;"
                                                src="{{ $value['image'] !='' ? asset($value['image']) : asset('uploads/placeholder/default_user.png')}}"></td>
                                        <td>{{ $value['title'] }}</td>
                                        <td>{{ substr($value['description'] ?? '', 0, 60) . '...' }}</td>
                                        <td>
                                            @if($value->role === 'parent')
                                                {{ trim(($value->storyAddedByParent?->first_name ?? '') . ' ' . ($value->storyAddedByParent?->last_name ?? '')) ?: '—' }}
                                            @elseif($value->role === 'kid')
                                                {{ $value->storyAddedByChild?->name ?? '—' }}
                                            @else
                                               admin
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->role === 'parent')
                                                {{ $value->role }}
                                            @elseif($value->role === 'kid')
                                                {{ $value->role }}
                                            @else
                                              admin
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($value->is_featured == 1)
                                            <a class="updateFeaturedStoryStatus" id="story-{{ $value['id']}}" story_id="{{ $value['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateFeaturedStoryStatus" id="story-{{ $value['id']}}" story_id="{{ $value['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :view-url="route('story.show', encrypt($value['id']))"
                                                :edit-url="route('story.edit', encrypt($value['id']))"
                                                delete-record="story-delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Stories"
                                                delete-title="Delete Story Page"
                                            />
                                        </td>
                                    </tr>
                                    @endforeach
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
