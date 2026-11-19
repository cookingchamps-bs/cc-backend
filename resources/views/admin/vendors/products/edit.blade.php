@extends($layout)
@section('content')
@php
    $panel = request()->is('admin/*') ? 'admin' : 'vendor';
@endphp
<div class="content-wrapper">
        <x-admin.page-header
        :title="$common['title'] ?? 'Title'"
        :home-url="$panel === 'vendor' ? route('vendor.dashboard') : url('admin/dashboard')"
    />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                                        <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Product'">
<form action="{{ route($panel. '.products.update', encrypt($products->id)) }}" name="category" id="main" 
                            method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('title') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Title*</label>
                                        <input
                                            class="form-control {{ $errors->has('title') ? 'form-control-danger' : '' }}"
                                            name="title" type="text"
                                            value="{{ old('title', $products->title) }}" placeholder="Enter Product">      
                                        @error('title')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('product_category') ? 'has-danger' : '' }}">
                                        <label for="product_category" class="col-form-label">Product Category*</label>
                                        <select class="form-control" id="product_category" name="product_category">
                                            <option value="" disabled selected>Select Category</option>
                                            @forelse($productCategories as $productCat)
                                                <option value="{{ $productCat->id }}" {{ $productCat->id == $products->product_cat_id ? 'selected' : '' }}>
                                                    {{ $productCat->name }}
                                                </option>
                                            @empty
                                                <option value="" disabled>No Category Found</option>
                                            @endforelse
                                        </select>
                                        @error('product_category')
                                            <div class="text-danger mt-1">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('image') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Image*</label>
                                        <input type="file"
                                            class="form-control {{ $errors->has('image') ? 'form-control-danger' : '' }}"
                                            onchange="loadFile(event,'image_1')" name="image">
                                        @error('image')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="media-left">
                                        <a href="#" class="profile-image">
                                            <img class="user-img img-css" id="image_1" width="30%;"
                                                src="{{ asset($products->image) }}">
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('gallery_images') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Gallery Image*</label>
                                        <input type="file"
                                            class="form-control {{ $errors->has('gallery_images') ? 'form-control-danger' : '' }}"
                                            onchange="loadFile(event,'image_1')" name="gallery_images[]" multiple>
                                        @error('gallery_images')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    <div class="media-left">
                                        @foreach($products->productImages as $val)
                                        <a href="#" class="profile-image">
                                            <img class="user-img img-css" id="image_1" width="30%;"
                                                src="{{ asset($val->images) }}">
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('quantity') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Quantity (In Numbers)*</label>
                                        <input
                                            class="form-control {{ $errors->has('quantity') ? 'form-control-danger' : '' }}"
                                            name="quantity" type="text"
                                            value="{{ old('quantity', $products->quantity) }}" placeholder="Enter Quantity">      
                                        @error('quantity')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('description') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Description*</label>
                                        <textarea class="form-control summernote {{ $errors->has('description') ? 'form-control-danger' : '' }}"
                                            name="description"
                                            value="{{ old('description') }}" placeholder="Enter description">{{$products->description}}</textarea>
                                        @error('description')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('selling_price') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Selling Price (Must be Integer)*</label>
                                        <input
                                            class="form-control {{ $errors->has('selling_price') ? 'form-control-danger' : '' }}"
                                            name="selling_price" type="text"
                                            value="{{ old('selling_price', $products->selling_price) }}" placeholder="Enter Selling Price">      
                                        @error('selling_price')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('cost_price') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Cost Price (Must be Integer)*</label>
                                        <input
                                            class="form-control {{ $errors->has('cost_price') ? 'form-control-danger' : '' }}"
                                            name="cost_price" type="text"
                                            value="{{ old('cost_price', $products->cost_price) }}" placeholder="Enter Cost Price">      
                                        @error('cost_price')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('discounted_price') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Discounted Price (Must be Integer)*</label>
                                        <input
                                            class="form-control {{ $errors->has('discounted_price') ? 'form-control-danger' : '' }}"
                                            name="discounted_price" type="text"
                                            value="{{ old('discounted_price', $products->discount_price) }}" placeholder="Enter Discounted Price">      
                                        @error('discounted_price')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <x-admin.form-actions
                                :submit-label="$common['button'] ?? 'Save'"
                                :cancel-url="route($panel . '.products.index')"
                            />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection