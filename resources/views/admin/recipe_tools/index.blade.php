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
                        :create-url="route('recipe-tool.create')"
                        create-label="Add recipe tool"
                    >
                        <div class="table-responsive">
                            <table id="qualification" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th class="text-center" style="width:64px">Icon</th>
                                        <th>Name</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recipe_tool as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center"><img class="tbl-img-css" width="50px" height="50px"  style="border-radius: 50%;"
                                                src="{{ $value['tool_image'] !='' ? asset($value['tool_image']) : asset('uploads/placeholder/default_user.png')}}"></td>
                                        <td>{{ $value['tool_name'] }}</td>
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :edit-url="route('recipe-tool.edit', encrypt($value['id']))"
                                                delete-record="recipe-tool-delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Recipe Tool"
                                                delete-title="Delete Recipe Tool Page"
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
