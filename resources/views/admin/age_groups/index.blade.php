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
                        :create-url="route('age-group.create')"
                        create-label="Add More"
                    >
                        <div class="table-responsive">
                            <table id="categories" class="table table-bordered table-hover">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Age Group</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ageGroups as $key => $value)
                                    <tr class="text-center">
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td>{{ $value->age_from }} - {{$value->age_to}}</td>
                                        <td>
                                            <x-admin.action-buttons
                                                :edit-url="route('age-group.edit', encrypt($value['id']))"
                                                delete-record="age-group-delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Age Group"
                                                delete-title="Delete Age Group"
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
