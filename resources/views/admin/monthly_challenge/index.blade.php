@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title']" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card
                        :title="$common['title']"
                        :create-url="route('monthly-challenge.create')"
                        create-label="Add Monthly Challenge"
                    >
                        <div class="table-responsive">
                            <table id="growing_activity" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Banner Image</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyChallenge as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center"><img class="tbl-img-css" width="50px" height="50px"  style="border-radius: 50%;"
                                                src="{{ $value['banner_image'] !='' ? asset($value['banner_image']) : asset('uploads/placeholder/default_user.png')}}"></td>
                                        <td>{{ $value['title'] }}</td>
                                        <td>{{ substr(strip_tags($value['description']) ?? '', 0, 60) . '...' }}</td>
                                        <td class="text-center">
                                            @if($value->status == 1)
                                            <a class="updateMonthlyChallengeStatus" id="monthly_challenge-{{ $value['id']}}" monthly_challenge_id="{{ $value['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateMonthlyChallengeStatus" id="monthly_challenge-{{ $value['id']}}" monthly_challenge_id="{{ $value['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :view-url="route('monthly-challenge.show', encrypt($value['id']))"
                                                :edit-url="route('monthly-challenge.edit', encrypt($value['id']))"
                                                delete-record="monthly-challenge-delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Monthly Challenge"
                                                delete-title="Delete Monthly Challenge Page"
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
