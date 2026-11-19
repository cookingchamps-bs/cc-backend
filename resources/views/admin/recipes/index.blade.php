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
                        :create-url="route('recipe.create')"
                        create-label="Add recipe"
                    >
                        <div class="table-responsive">
                            <table id="qualification" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="width:56px">#</th>
                                        <th class="text-center" style="width:64px">Image</th>
                                        <th>Recipe Name</th>
                                        <th>Added By</th>
                                        <th>Role</th>
                                        <th>Recipe of the Month</th>
                                        <th>Popular Recipe</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recipes as $key => $value)
                                    <tr>
                                        <td class="text-center text-muted">{{ ++$key }}</td>
                                        <td class="text-center"><img class="tbl-img-css" width="50px" height="50px"  style="border-radius: 50%;"
                                                src="{{ $value['banner_image'] !='' ? asset($value['banner_image']) : asset('uploads/placeholder/default_user.png')}}"></td>
                                        <td>{{ $value['name'] }}</td>
                                        <td>
                                            @if($value->role === 'parent')
                                                @if($value->recipeAddedByParent)
                                                    {{ trim($value->recipeAddedByParent->first_name.' '.$value->recipeAddedByParent->last_name) }}
                                                @else
                                                    —
                                                @endif
                                            @elseif($value->role === 'kid')
                                                {{ optional($value->recipeAddedByChild)->name ?? '—' }}
                                            @else
                                               admin
                                            @endif
                                        </td>
                                        <td>
                                            @if($value->role === 'parent')
                                                {{ $value->role }}
                                            @elseif($value->role === 'kid')
                                                {{ $value->role }}
                                            @else
                                               admin
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($value->recipe_of_the_week == 1)
                                            <a class="updateRecipeOftheWeekStatus" id="recipe-{{ $value['id']}}" recipe_id="{{ $value['id'] }}" href="javascript:;void(0)" style='color:#3f6ed3'><i class="fas fa-toggle-on" status="Active"></i>
                                            </a>
                                            @else
                                            <a class="updateRecipeOftheWeekStatus" id="recipe-{{ $value['id']}}" recipe_id="{{ $value['id']}}" style="color:gray" href="javascript:;void(0)"><i class="fas fa-toggle-off" status="Inactive"></i>
                                            </a>
                                            @endif
                                        </td>
                                        <td>
                                            <select class="statusChange form-control" data-id="{{ $value->id }}" name="is_featured">
                                                <option value="1" {{ $value->status == "1" ? 'selected' : '' }}> Yes </option>
                                                <option value="0" {{ $value->status == "0" ? 'selected' : '' }}> No </option>
                                            </select>
                                        </td>
                                        <td class="text-center">
                                            <x-admin.action-buttons
                                                :view-url="route('recipe.show', encrypt($value['id']))"
                                                :edit-url="route('recipe.edit', encrypt($value['id']))"
                                                delete-record="recipe-delete"
                                                :delete-id="encrypt($value['id'])"
                                                delete-name="Recipe"
                                                delete-title="Delete recipe Page"
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
