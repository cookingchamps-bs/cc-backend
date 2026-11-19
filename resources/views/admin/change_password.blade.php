@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header :title="$common['title'] ?? 'Change Password'" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Change Password'">
                        <form action="{{ url('admin/change-password')}}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="current_pwd">Old Password</label>
                                <input type="password" class="form-control" id="current_pwd" name="current_pwd" required="" placeholder="Enter old password"><span id="vefiryCurrentPwd"></span>
                            </div>
                            <div class="form-group">
                                <label for="new_pwd">New Password</label>
                                <input type="password" class="form-control" id="new_pwd" name="new_pwd" required="" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_pwd">Confirm Password</label>
                                <input type="password" class="form-control" id="confirm_pwd" name="confirm_pwd" required="" placeholder="Enter confirm password">
                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="url('admin/dashboard')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
