@extends('school_admin.layouts.master')
@section('content')
<div class="content-wrapper">
    <x-admin.page-header title="Videos" :home-url="route('school.dashboard')" />
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card
                        title="Video list"
                        :create-url="route('add.video')"
                        create-label="Add video"
                    >
                        <div class="table-responsive">
                            <table id="growing_activity" class="table table-bordered table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th width="5%">#</th>
                                        <th>Title</th>
                                        <th>Description</th>
                                        <th width="10%">Status</th>
                                        <th width="15%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @forelse ($data as $key => $video)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td class="font-weight-bold">{{ $video->title }}</td>
                                        <td class="text-muted">{{ Str::limit($video->description, 60) }}</td>
                                        <td>
                                            @if($video->is_active == 1)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="cc-actions">
                                                <a href="{{ route('school.videos.edit', $video->id) }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('school.videos.delete', $video->id) }}"
                                                      method="POST"
                                                      class="d-inline school-video-delete"
                                                      style="display:inline;">
                                                    @csrf
                                                    <button type="submit" class="btn btn-link p-0 cc-action-delete" title="Delete" style="width:34px;height:34px;color:#DC2626;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <x-admin.empty-state message="No videos found" icon="fa-video" />
                                        </td>
                                    </tr>
                                @endforelse
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
$(function(){
    $('.school-video-delete').on('submit', function(e){
        e.preventDefault();
        var form = this;
        Swal.fire({
            title: 'Delete video?',
            text: 'This cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#48335C',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Yes, delete'
        }).then(function(result){
            if(result.isConfirmed){ form.submit(); }
        });
    });
});
</script>
@endpush
