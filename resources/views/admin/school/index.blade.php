@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="Schools" :home-url="url('admin/dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card
                        title="Schools"
                        :create-url="route('admin.add-school')"
                        create-label="Add School"
                    >
                        <div class="table-responsive">
                            <table id="growing_activity" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th>School Name</th>
                                        <th>Email</th>
                                        <th>School image/logo</th>
                                        <th>Contact person name</th>
                                        <th class="text-center">Videos</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ $key + 1 }}</td>
                                        <td>{{ $value->school_name }}</td>
                                        <td>{{ $value->email }}</td>
                                        <td>
                                            @if($value->school_logo)
                                                <img src="{{ asset($value->school_logo) }}"
                                                 width="50" height="50"
                                                 style="object-fit:cover;border-radius:6px;"
                                                >
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $value->contact_person_name }}</td>
                                        <td class="text-center">{{ $value->videos_count }}</td>
                                        <td class="text-center">
                                            @if($value->isAccountActive())
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Deactivated</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="cc-actions">
                                                <a href="{{ route('view.school', $value->id) }}" title="View">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="{{ route('edit-school', $value->id) }}" title="Edit">
                                                    <i class="fa-solid fa-pencil"></i>
                                                </a>
                                                <form action="{{ route('change-status', $value->id) }}" method="POST" style="display:inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-warning">
                                                        {{ $value->status ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.school.delete', $value->id) }}"
                                                      method="POST"
                                                      style="display:inline"
                                                      onsubmit="return confirm('Delete this school?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
