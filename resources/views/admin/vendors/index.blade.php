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
                        :create-url="route('vendors.create')"
                        create-label="Add Vendors"
                    >
                        <div class="table-responsive">
                            <table id="growing_activity" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>Username</th>
                                        <th>Name</th>
                                        <th>Mobile number</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($vendors as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td>{{ $value->username }}</td>
                                        <td>{{ $value->name ?? '—' }}</td>
                                        <td>{{ $value->mobile_no ?? '—' }}</td>
                                        <td class="text-center">
                                            <div class="cc-actions">
                                                <a href="{{ route('vendors.show', encrypt($value['id'])) }}" title="View details">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="{{ route('vendors.edit', encrypt($value['id'])) }}" title="Edit">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </a>
                                                <form method="POST" action="{{ route('vendor.delete', encrypt($value['id'])) }}" style="display:inline;" onsubmit="return confirm('This will delete vendor and all products. Continue?')">
                                                    @csrf
                                                    <button type="submit" title="Delete vendor" class="cc-action-delete border-0 bg-transparent p-0">
                                                       <i class="fa-solid fa-trash"></i>
                                                    </button>
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
