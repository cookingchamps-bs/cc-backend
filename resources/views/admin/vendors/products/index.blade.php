@extends($layout)
@section('content')
@php
    $panel = request()->is('admin/*') ? 'admin' : 'vendor';
@endphp
<div class="content-wrapper">
    <x-admin.page-header
        :title="$common['title']"
        :home-url="$panel === 'vendor' ? route('vendor.dashboard') : url('admin/dashboard')"
    />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card
                        :title="$common['title']"
                        :create-url="route($panel . '.products.create')"
                        create-label="Add Products"
                    >
                        <div class="table-responsive">
                            <table id="categories" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th class="text-center" style="width:64px">Image</th>
                                        <th>Title</th>
                                        <th>Product Category</th>
                                        <th>Added By</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($products as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center">
                                           <img class="tbl-img-css rounded-circle" width="40" height="40" src="{{ asset($value->image) }}">
                                        </td>
                                        <td>{{ $value->title }} </td>
                                        <td>
                                            {{ $value->productCategory->name ?? '-' }}
                                        </td>
                                        <td>
                                           {{$value->vendor->username  ?? 'Admin' }}
                                        </td>
                                        <td>
                                            @if($value['status'] == 1)
                                            <a class="updateProductStatus" id="product-{{ $value['id']}}" product_id="{{ $value['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateProductStatus" id="product-{{ $value['id']}}" product_id="{{ $value['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="cc-actions">
                                                <a href="{{ route($panel . '.products.edit', encrypt($value['id'])) }}" title="Edit">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </a>
                                                <form id="delete-form-{{ $value->id }}" method="POST" action="{{ route($panel . '.products.destroy', encrypt($value->id)) }}" style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <a href="javascript:void(0)" onclick="confirmDelete({{ $value->id }})"
                                                       title="Delete Product"
                                                       class="cc-action-delete">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </a>
                                                </form>
                                            </div>
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
@push('script')
<script>
function confirmDelete(id) {
    if (confirm("Are you sure you want to delete this product?")) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
<script>
$(document).on("click", ".updateProductStatus", function () {

    var product_id = $(this).attr("product_id");

    $.ajax({
        url: "{{ route($panel . '.update-product-status') }}",
        type: "post",
        data: {
            product_id: product_id,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (resp) {

            if (resp.status == 1) {
                $("#product-" + product_id).html('<i class="fas fa-toggle-on" status="Active"></i>');
            } else {
                $("#product-" + product_id).html('<i class="fas fa-toggle-off" status="Inactive"></i>');
            }

        }
    });

});
</script>
@endpush
