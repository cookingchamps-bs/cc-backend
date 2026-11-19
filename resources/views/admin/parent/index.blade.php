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
                            <table id="companyActivity" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th class="text-center" style="width:64px">Icon</th>
                                        <th>Name</th>
                                        <th>Role</th>
                                        <th>Email</th>
                                        {{--<th>Status</th>--}}
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $key => $user)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center">
                                           <img class="tbl-img-css rounded-circle" width="40" height="40" src="{{ asset($user->image) }}">
                                        </td>
                                        <td>{{ $user->first_name }} {{ $user->last_name}}</td>
                                        <td>{{ $user->role }}</td>
                                        <td>{{ $user->email}}</td>
                                        {{--<td>
                                            @if($user['status'] == 1)
                                            <a class="updateCompanyActivityStatus" id="activity-{{ $user['id']}}" activity_id="{{ $user['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateCompanyActivityStatus" id="activity-{{ $user['id']}}" activity_id="{{ $user['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>--}}
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :view-url="route('parent.show', encrypt($user['id']))"
                                                :edit-url="route('parent.edit', encrypt($user['id']))"
                                                delete-record="parent-delete"
                                                :delete-id="encrypt($user['id'])"
                                                delete-name="Parent"
                                                delete-title="Delete Parent"
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
