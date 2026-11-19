@extends('admin.layouts.master')
@section('content')
<div class="content-wrapper">
        <x-admin.page-header :title="$common['title'] ?? 'Title'" :home-url="url('admin/dashboard')" />
<section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <x-admin.data-card :title="$common['heading_title'] ?? $common['title'] ?? 'Edit Recipe'">
<form action="{{ route('recipe.update', [$recipes->id]) }}" name="PrivacyPoliciesDetailForm" id="main" 
                            method="post" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_name') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Recipe Name*</label>
                                        <input
                                            class="form-control {{ $errors->has('recipe_name') ? 'form-control-danger' : '' }}"
                                            name="recipe_name" type="text"
                                            value="{{ old('recipe_name', value($recipes->name)) }}" placeholder="Enter Recipe">      
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
                                                <option value="{{ $val->id }}" {{ in_array($val->id, $selectedCategories) ? 'selected' : '' }}>
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
                                                <option value="{{ $val->id }}" {{ in_array($val->id, $selectedAgeGroup) ? 'selected' : '' }}>
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
                                    <div class="media-left">
                                        <a href="#" class="profile-image">
                                            <img class="user-img img-css" id="image_1" width="30%;"
                                                src="{{ asset($recipes->banner_image)}}">
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_tool') ? 'has-danger' : '' }}">
                                        <label class="col-form-label">Recipe Tool*</label>
                                        <!-- Add the 'multiple' attribute to allow multiple selections -->
                                        <select class="form-control" id="recipe_tool" name="recipe_tool[]" multiple>
                                            <option value="">Select Recipe Tool</option>
                                            @forelse($recipeTool as $val)
                                                <option value="{{ $val->id }}" {{ in_array($val->id, explode(",", $recipes->recipe_tools)) ? 'selected' : '' }}>
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
                                            value="{{ old('video_link', value($recipes->video_link)) }}" placeholder="Enter video link">      
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
                                    
                                      {{-- Existing ingredients (edit page) --}}
                                      <div id="ingredients-container">
                                        @foreach($recipes->recipeIngredient as $ingredient)
                                          <div class="ingredient-group d-flex align-items-center mb-2">
                                            <div class="col-md-8 pe-2">
                                              <input
                                                class="form-control {{ $errors->has('recipe_ingredients') ? 'form-control-danger' : '' }}"
                                                name="recipe_ingredients[]" type="text"
                                                value="{{ $ingredient->name }}"
                                                placeholder="Enter Recipe Ingredients">
                                            </div>
                                            <div class="col-md-4 d-flex">
                                              <button type="button" class="btn btn-danger remove-ingredient me-2">Remove</button>
                                            </div>
                                          </div>
                                        @endforeach
                                      </div>
                                    
                                      {{-- Always-present template --}}
                                      <template id="ingredients-template">
                                        <div class="ingredient-group d-flex align-items-center mb-2">
                                          <div class="col-md-8 pe-2">
                                            <input class="form-control" name="recipe_ingredients[]" type="text" placeholder="Enter Recipe Ingredients">
                                          </div>
                                          <div class="col-md-4 d-flex">
                                            <button type="button" class="btn btn-danger remove-ingredient me-2">Remove</button>
                                          </div>
                                        </div>
                                      </template>
                                    
                                      <button type="button" class="btn btn-primary mt-2" id="add-more-ingredients">Add More</button>
                                    
                                      @error('recipe_ingredients')
                                        <div class="col-form-alert-label">{{ $message }}</div>
                                      @enderror
                                    </div>
                                </div>
                                
                                <!--RECIPE STEPS-->
                                <div class="col-md-12">
                                    <div class="form-group mb-3 {{ $errors->has('recipe_steps') ? 'has-danger' : '' }}">
                                      <label class="col-form-label">Recipe Steps*</label>
                                    
                                      <div id="steps-container">
                                        @foreach($recipes->recipeSteps as $steps)
                                          <div class="steps-group d-flex align-items-center mb-2">
                                            <div class="col-md-8 pe-2">
                                              <input
                                                class="form-control {{ $errors->has('recipe_steps') ? 'form-control-danger' : '' }}"
                                                name="recipe_steps[]" type="text"
                                                value="{{ $steps->name }}"
                                                placeholder="Enter Recipe Steps">
                                            </div>
                                            <div class="col-md-4 d-flex">
                                              <button type="button" class="btn btn-danger remove-steps me-2">Remove</button>
                                            </div>
                                          </div>
                                        @endforeach
                                      </div>
                                    
                                      {{-- Always-present template for new steps --}}
                                      <template id="steps-template">
                                        <div class="steps-group d-flex align-items-center mb-2">
                                          <div class="col-md-8 pe-2">
                                            <input class="form-control" name="recipe_steps[]" type="text" placeholder="Enter Recipe Steps">
                                          </div>
                                          <div class="col-md-4 d-flex">
                                            <button type="button" class="btn btn-danger remove-steps me-2">Remove</button>
                                          </div>
                                        </div>
                                      </template>
                                    
                                      <button type="button" class="btn btn-primary mt-2" id="add-more-steps">Add More</button>
                                    
                                      @error('recipe_steps')
                                        <div class="col-form-alert-label">{{ $message }}</div>
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
    // Initialize Select2 on the select element with multiple option
    $(document).ready(function() {
        $('#recipe_tool').select2({
            placeholder: "Select Recipe Tool",
            allowClear: true  // Optional: Allows clearing of selections
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
    // -------------- RECIPE STEPS --------------- //
    const stepsContainer = document.getElementById('steps-container');
    const template = document.getElementById('steps-template');
    
    document.getElementById('add-more-steps').addEventListener('click', () => {
      const clone = document.importNode(template.content, true);
      stepsContainer.appendChild(clone);
    });
    
    stepsContainer.addEventListener('click', event => {
      if (!event.target.classList.contains('remove-steps')) return;
    
      const groups = stepsContainer.querySelectorAll('.steps-group');
      if (groups.length === 1) {
        return alert('At least one step field is required.');
      }
    
      const toRemove = event.target.closest('.steps-group');
      if (toRemove) toRemove.remove();
    });
    
    
    // ---------------- RECIPE INGREDIENTS ---------------- //
    const ingrContainer = document.getElementById('ingredients-container');
    const ingrTemplate  = document.getElementById('ingredients-template');
      
    document.getElementById('add-more-ingredients').addEventListener('click', () => {
        const clone = document.importNode(ingrTemplate.content, true);
        ingrContainer.appendChild(clone);
    });
      
    ingrContainer.addEventListener('click', e => {
        if (!e.target.classList.contains('remove-ingredient')) return;
        
        const groups = ingrContainer.querySelectorAll('.ingredient-group');
        if (groups.length === 1) return alert('At least one ingredient field is required.');
        e.target.closest('.ingredient-group').remove();
    });
</script>
@endpush