@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Create Recipe'">
<form action="{{ route('recipe.store') }}" name="PrivacyPoliciesDetailForm" id="main" 
                            method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Recipe Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('recipe_name') ? 'form-control-danger' : '' }}"
                                            name="recipe_name" type="text"
                                            value="{{ old('recipe_name') }}" placeholder="Enter Recipe">      
                                        @error('recipe_name')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_category') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Category*</label>
                                        <!-- Add the 'multiple' attribute to allow multiple selections -->
                                        <select class="form-control" id="recipe_category" name="recipe_category[]" multiple>
                                            <option value="">Select Category</option>
                                            @forelse($categories as $val)
                                                <option value="{{ $val->id }}" {{ in_array($val->id, old('recipe_category', [])) ? 'selected' : '' }}>
                                                    {{ $val->name }}
                                                </option>
                                            @empty
                                                <option disabled>No Category Available</option>
                                            @endforelse
                                        </select>    
                                        @error('recipe_category')
                                            <div class="col-form-alert-label text-danger">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('age_group') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Age Group*</label>
                                        <!-- Add the 'multiple' attribute to allow multiple selections -->
                                        <select class="form-control" id="age_group" name="age_group[]" multiple>
                                            <option value="">Select Age Group</option>
                                            @forelse($ageGroup as $val)
                                                <option value="{{ $val->id }}" {{ in_array($val->id, old('age_group', [])) ? 'selected' : '' }}>
                                                    {{ $val->age_from }} - {{ $val->age_to }}
                                                </option>
                                            @empty
                                                <option disabled>No Age Group Available</option>
                                            @endforelse
                                        </select>    
                                        @error('age_group')
                                            <div class="col-form-alert-label text-danger">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('image') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Banner Image*</label>
                                        <input type="file"
                                            class="form-control {{ $errors->has('image') ? 'form-control-danger' : '' }}"
                                            onchange="loadFile(event,'image_1')" name="image">
                                        @error('image')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                    {{--<div class="media-left">
                                        <a href="#" class="profile-image">
                                            <img class="user-img img-css" id="image_1" width="30%;"
                                                src="{{ asset('storage/logo.png')}}">
                                        </a>
                                    </div>--}}
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_tool') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Recipe Tool*</label>
                                        <!-- Add the 'multiple' attribute to allow multiple selections -->
                                        <select class="form-control" id="recipe_tool" name="recipe_tool[]" multiple>
                                            <option value="">Select Recipe Tool</option>
                                            @forelse($recipeTool as $val)
                                                <option value="{{ $val->id }}" {{ in_array($val->id, old('recipe_tool', [])) ? 'selected' : '' }}>
                                                    {{ $val->tool_name }}
                                                </option>
                                            @empty
                                                <option disabled>No Tools Available</option>
                                            @endforelse
                                        </select>    
                                        @error('recipe_tool')
                                            <div class="col-form-alert-label text-danger">
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
                                            value="{{ old('video_link') }}" placeholder="Enter video link">      
                                        @error('video_link')
                                            <div class="col-form-alert-label">
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                                
                               <div class="col-md-12">
                                    <div class="form-group {{ $errors->has('recipe_ingredients') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Recipe Ingredients*</label>
                                        <div id="ingredients-container">
                                            <div class="ingredient-group d-flex align-items-center mb-2">
                                                <div class="col-md-8 pe-2">
                                                    <input class="form-control {{ $errors->has('recipe_ingredients') ? 'form-control-danger' : '' }}" 
                                                           name="recipe_ingredients[]" type="text" 
                                                           value="" placeholder="Enter Recipe Ingredients">
                                                </div>
                                                <div class="col-md-4 d-flex">
                                                    <button type="button" class="btn btn-danger remove-ingredient me-2">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary mt-2" id="add-more">Add More</button>
                                
                                        @error('recipe_ingredients')
                                        <div class="col-form-alert-label">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_steps') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Recipe Steps*</label>
                                        <div id="steps-container">
                                            <div class="steps-group d-flex align-items-center mb-2">
                                                <div class="col-md-8 pe-2">
                                                    <input class="form-control {{ $errors->has('recipe_steps') ? 'form-control-danger' : '' }}" 
                                                           name="recipe_steps[]" type="text" 
                                                           value="" placeholder="Enter Recipe Steps">
                                                </div>
                                                <div class="col-md-4 d-flex">
                                                    <button type="button" class="btn btn-danger remove-steps me-2">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary mt-2" id="add-more-steps">Add More</button>
                                
                                        @error('recipe_steps')
                                        <div class="col-form-alert-label">
                                            {{ $message }}
                                        </div>
                                        @enderror
                                    </div>
                                </div>

                            </div>
                            <x-admin.form-actions :submit-label="$common['button'] ?? 'Save'" :cancel-url="route('recipe.index')" />
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
    // Initialize Select2
    $(document).ready(function() {
        $('#recipe_tool').select2({
            placeholder: "Select Recipe Tool",
            allowClear: true
        });
    });
    
    $(document).ready(function() {
        $('#recipe_category').select2({
            placeholder: "Select Category",
            allowClear: true
        });
    });
    
    $(document).ready(function() {
        $('#age_group').select2({
            placeholder: "Select Age Group",
            allowClear: true
        });
    });
</script>
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
@endpush

