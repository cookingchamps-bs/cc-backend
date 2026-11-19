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
                        :create-url="route('product-categories.create')"
                        create-label="Add More"
                    >
                        <div class="table-responsive">
                            <table id="product_categories" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th class="text-center" style="width:64px">Image</th>
                                        <th>Name</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productCategories as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center">
                                           <img class="tbl-img-css rounded-circle" width="40" height="40" src="{{ asset($value->image) }}">
                                        </td>
                                        <td>{{ $value->name }} </td>
                                        <td class="text-center">
                                            @if($value['status'] == 1)
                                            <a class="updateProductCategoryStatus" id="category-{{ $value['id']}}" activity_id="{{ $value['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateProductCategoryStatus" id="category-{{ $value['id']}}" activity_id="{{ $value['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :edit-url="route('product-categories.edit', encrypt($value['id']))"
                                                delete-record="product-categories-delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Product Category"
                                                delete-title="Delete Product Category Page"
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
