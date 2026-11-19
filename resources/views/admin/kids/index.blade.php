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
                                        <th>Parent</th>
                                        <th>Role</th>
                                        <th>DOB</th>
                                        <th>Grade</th>
                                        {{--<th>Status</th>--}}
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($kids as $key => $kid)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center">
                                           <img class="tbl-img-css rounded-circle" width="40" height="40" src="{{ asset($kid->image) }}">
                                        </td>
                                        <td>{{ $kid->name }} </td>
                                        <td>
                                            @php
                                                $parentDetail = \App\Models\User::select('first_name', 'last_name')->where('id', $kid->parent_id)->first();
                                            @endphp
                                            @if($parentDetail)
                                                {{ $parentDetail->first_name }} {{ $parentDetail->last_name }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $kid->role }}</td>
                                        <td>{{ $kid->day }} | {{ $kid->month}} | {{ $kid->year}}</td>
                                        <td>{{ $kid->grade}}</td>
                                        {{--<td>
                                            @if($kid['status'] == 1)
                                            <a class="updateCompanyActivityStatus" id="activity-{{ $kid['id']}}" activity_id="{{ $kid['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateCompanyActivityStatus" id="activity-{{ $kid['id']}}" activity_id="{{ $kid['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>--}}
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :view-url="route('kid.show', encrypt($kid['id']))"
                                                :edit-url="route('kid.edit', encrypt($kid['id']))"
                                                delete-record="kid-delete"
                                                :delete-id="encrypt($kid['id'])"
                                                delete-name="Kid"
                                                delete-title="Delete Kid"
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
