@extends('school_admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="Change password" :home-url="route('school.dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-md-8 col-lg-6">
                    <x-admin.data-card title="Change password">
                        <form action="{{ route('store-changepassword') }}" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="current_pwd">Old password</label>
                                <input type="password" class="form-control" id="current_pwd" name="current_password" required placeholder="Enter old password">
                                <span id="vefiryCurrentPwd"></span>
                            </div>
                            <div class="form-group">
                                <label for="new_pwd">New password</label>
                                <input type="password" class="form-control" id="new_pwd" name="new_password" required placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label for="confirm_pwd">Confirm password</label>
                                <input type="password" class="form-control" id="confirm_pwd" name="confirm_pwd" required placeholder="Enter confirm password">
                            </div>
                            <x-admin.form-actions submit-label="Update password" :cancel-url="route('school.dashboard')" />
                        </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
