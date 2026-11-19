@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Edit Story'">
<form action="{{ route('story.update', [$story->id]) }}" name="PrivacyPoliciesDetailForm" id="main" 
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
                                            value="{{ old('title', value($story->title)) }}" placeholder="Enter story title">      
                                        @error('title')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('description') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Description*</label>
                                        <textarea class="form-control {{ $errors->has('description') ? 'form-control-danger' : '' }}"
                                            name="description" placeholder="Enter description">{{ value($story->description) }}</textarea>
                                        @error('description')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('video_link') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Video Link*</label>
                                        <input
                                            class="form-control {{ $errors->has('video_link') ? 'form-control-danger' : '' }}"
                                            name="video_link" type="url"
                                            value="{{ old('video_link', value($story->video_link)) }}" placeholder="Enter video link">      
                                        @error('video_link')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('image') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Story Image*</label>
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
                                                src="{{ asset($story->image)}}">
                                        </a>
                                    </div>
                                </div>

                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('story.index')" />
                            </form>
                    </x-admin.data-card>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
@push('script')
<script>
    document.getElementById('add-more').addEventListener('click', function() {
        const ingredientsContainer = document.getElementById('ingredients-container');
        const ingredientGroups = ingredientsContainer.querySelectorAll('.ingredient-group');
        
        // Clone the first ingredient group
        const ingredientGroup = ingredientGroups[0];
        const newIngredientGroup = ingredientGroup.cloneNode(true);

        // Clear the input value for the new field
        newIngredientGroup.querySelector('input').value = '';

        // Append the new ingredient group
        ingredientsContainer.appendChild(newIngredientGroup);
    });

    // Event delegation to handle the remove button
    document.getElementById('ingredients-container').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('remove-ingredient')) {
            const ingredientGroups = document.querySelectorAll('.ingredient-group');
            
            // Check if there's more than one field, then remove the clicked one
            if (ingredientGroups.length > 1) {
                event.target.closest('.ingredient-group').remove();
            } else {
                alert("At least one ingredient field is required.");
            }
        }
    });
</script>
<script>
      document.getElementById('add-more-steps').addEventListener('click', function() {
        const stepsContainer = document.getElementById('steps-container');
        const stepsGroups = stepsContainer.querySelectorAll('.steps-group');
        
        // Clone the first steps group
        const stepsGroup = stepsGroups[0];
        const newStepsGroup = stepsGroup.cloneNode(true);

        // Clear the input value for the new field
        newStepsGroup.querySelector('input').value = '';

        // Append the new steps group
        stepsContainer.appendChild(newStepsGroup);
    });

    // Event delegation to handle the remove button
    document.getElementById('steps-container').addEventListener('click', function(event) {
        if (event.target && event.target.classList.contains('remove-steps')) {
            const stepsGroups = document.querySelectorAll('.steps-group');
            
            // Check if there's more than one field, then remove the clicked one
            if (stepsGroups.length > 1) {
                event.target.closest('.steps-group').remove();
            } else {
                alert("At least one step field is required.");
            }
        }
    });
</script>
<script>
    // Initialize Select2 on the select element with multiple option
    $(document).ready(function() {
        $('#recipe_tool').select2({
            placeholder: "Select Recipe Tool",
            allowClear: true  // Optional: Allows clearing of selections
        });
    });
</script>
@endpush

