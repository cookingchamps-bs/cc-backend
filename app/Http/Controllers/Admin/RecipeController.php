<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\{Recipe,RecipeIngredient,RecipeStep,Category};
use App\Models\{RecipeTool,AgeGroup,RecipeCategory,RecipeAgeGroup};
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RecipeController extends Controller
{
    /*--------------------------------- Display a listing of the resource.---------------------------------*/
    public function index()
    {
        $common = Helper::getCommonData('Recipes List');
        $recipes = Recipe::with([
            'recipeIngredient',
            'recipeSteps',
            'recipeAddedByParent',
            'recipeAddedByChild',
        ])->latest()->get();

        return view('admin.recipes.index', compact('common', 'recipes'));
    }

    /*------------------------------- Show the form for creating a new resource.------------------------------*/
    public function create(Request $request)
    {
        $recipeTool = RecipeTool::all();
        $categories = Category::all();
        $ageGroup = AgeGroup::all();
        
        $common = Helper::getCommonData('Add Recipe', 'Add Recipe', 'Submit');
    
        return view('admin.recipes.create', compact('common','recipeTool','categories','ageGroup'));
    }

    /*---------------------------- Store a newly created resource in storage.-----------------------------------*/
    public function store(Request $request)
    {
        $request->validate([
            'recipe_name' => 'required',
            'image'   => 'required|mimes:jpeg,jpg,png',
            'recipe_tool' => 'required',
            'recipe_ingredients' => 'required',
            'recipe_steps' => 'required'
        ]);
        
        // $adminDetails = Auth::guard('admin')->user();
        $recipeTools = implode(",", $request->recipe_tool);
        
        //------------ insert recipe ----------------------------------------------- //
        $recipe = Recipe::create([
            'role' => 'admin',
            'name' => $request->recipe_name,
            'video_link' => $request->video_link,
            'banner_image' => Helper::handleFileUpload($request, 'image', 'recipes', $existingPath = null),
            'recipe_tools' => $recipeTools,
        ]);
        
        // ---------------- insert categories -------------------------//
        $recipeCategories =  $request->recipe_category;
        foreach ($recipeCategories as $recipeCat) {
            if($recipeCat == ''){
                continue;
            }
            
            RecipeCategory::create([
                'recipe_id' => $recipe->id,
                'cat_id' => $recipeCat
            ]);
        }
        
        // ---------------- insert Age Group -------------------------//
        $ageGroup =  $request->age_group;
        foreach ($ageGroup as $age) {
            if($age == ''){
                continue;
            }
            
            RecipeAgeGroup::create([
                'recipe_id' => $recipe->id,
                'age_group_id' => $age
            ]);
        }
        
        // ---------------- insert multiple recipe ingredients -------------------------//
        $Ingredients =  $request->recipe_ingredients;
        foreach ($Ingredients as $val) {
            if($val == ''){
                continue;
            }
            RecipeIngredient::create([
                'recipe_id' => $recipe->id,
                'name' => trim($val),
            ]);
        }
        
        // ---------------- insert multiple recipe steps ------------------------------//
        $RecipeSteps = $request->recipe_steps;
        foreach ($RecipeSteps as $step) {
            if($step == ''){
                continue;
            }
            RecipeStep::create([
                'recipe_id' => $recipe->id,
                'name' => trim($step),
            ]);
        }
        
        $notification = [
            'message' => 'Recipe added Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('recipe.index', $notification);
    }
    
    /*------------------------- Display the specified resource.----------------------------------------------*/
    public function show(string $id)
    {
        $recipes = Recipe::with([
            'recipeCategories.category',
            'recipeAgeGroup.ageGroup',
            'recipeIngredient',
            'recipeSteps',
            'recipeAddedByParent',
            'recipeAddedByChild',
        ])->findOrFail(decrypt($id));

        $toolIds = array_filter(explode(',', (string) $recipes->recipe_tools));
        $recipeTools = $toolIds !== []
            ? RecipeTool::whereIn('id', $toolIds)->get(['id', 'tool_name', 'tool_image'])
            : collect();
        $recipes->setRelation('recipeToolsList', $recipeTools);

        return view('admin.recipes.show', compact('recipes'));
    }
    
    /* ------------ Edit Recipe -----------*/
    public function edit(string $id)
    {
        $common = Helper::getCommonData('Edit Recipe', 'Edit Recipe', 'Update');
        $recipeTool = RecipeTool::all();
        $categories = Category::all();
        $ageGroup = AgeGroup::all();
        $recipes = Recipe::with(['recipeIngredient','recipeCategories','recipeAgeGroup'])->find(decrypt($id));

        // selected categories
        $selectedCategories = $recipes->recipeCategories->pluck('cat_id')->toArray();
        $selectedAgeGroup = $recipes->recipeAgeGroup->pluck('age_group_id')->toArray();
        
        return view('admin.recipes.edit', compact('common', 'recipes','recipeTool','categories',
        'selectedCategories','ageGroup','selectedAgeGroup'));
    }
    
    /* ------------ update Recipe --------- */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'recipe_name'  => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png',
            'recipe_tool' => 'required',
            'recipe_ingredients' => 'required',
            'recipe_steps' => 'required'
        ]);
        
        $recipe = Recipe::find($id);
       
        $recipeTools = implode(",", $request->recipe_tool);
        
        //------------ Update recipe -----------------------------------------------
        $recipe->update([
            'name' => $request->recipe_name ?? $recipe->name,
            'video_link' => $request->video_link ?? $recipe->video_link,
            'banner_image' => $request->hasFile('image') ? Helper::handleFileUpload($request, 'image', 'recipes', $existingPath = null) : $recipe->banner_image,
            'recipe_tools' => $recipeTools ?? $recipe->recipe_tools,
        ]);
        
        // ---------------- update categories ---------------------------------- //
        if($request->filled('recipe_category')) {
            $recipeCategories =  $request->recipe_category;
            RecipeCategory::where('recipe_id', $id)->delete();
            foreach ($recipeCategories as $recipeCat) {
                if($recipeCat == ''){
                    continue;
                }
                RecipeCategory::create([
                    'recipe_id' => $recipe->id,
                    'cat_id'    => $recipeCat,
                ]);
            }
        }
        
        // ---------------- update Age Group ---------------------------------- //
        if($request->filled('age_group')) {
            $recipeAgeGroup =  $request->age_group;
            RecipeAgeGroup::where('recipe_id', $id)->delete();
            foreach ($recipeAgeGroup as $ageGrp) {
                if($ageGrp == ''){
                    continue;
                }
                RecipeAgeGroup::create([
                    'recipe_id' => $recipe->id,
                    'age_group_id' => $ageGrp
                ]);
            }
        }
        
        // ---------------- insert multiple recipe ingredients --------------------------------------------- 
        if($request->filled('recipe_ingredients')) {
            $Ingredients =  $request->recipe_ingredients;
            RecipeIngredient::where('recipe_id', $id)->delete();
            foreach ($Ingredients as $val) {
                if($val == ''){
                    continue;
                }
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'name' => trim($val),
                ]);
            }
            
        }
        
        // ---------------- insert multiple recipe steps --------------------------------------------- 
        if(isset($request->recipe_steps)) {
            $RecipeSteps = $request->recipe_steps;
            RecipeStep::where('recipe_id', $id)->delete();
            foreach ($RecipeSteps as $step) {
                if($step == ''){
                    continue;
                }
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'name' => trim($step),
                ]);
            }
        }
        
        $notification = [
            'message' => 'Recipe Updated Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('recipe.index', $notification);
    }

    
    /*-------------------------------- Remove the specified resource from storage.--------------------------------*/
    public function destroy(string $id)
    {
        try {
            $recipe = Recipe::findOrFail(decrypt($id));

            Helper::deletePublicFile($recipe->getRawOriginal('banner_image') ?? $recipe->banner_image);

            $recipe->delete();

            $notification = [
                'message' => 'Recipe deleted Successfully!',
                'alert-type' => 'success',
            ];

            return Helper::redirectFunction('recipe.index', $notification);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Recipe delete failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to delete recipe.'], 500);
        }
    }

    /* -------------------- update popular recipe status -----------------------------------------------------*/
    public function updatePopularRecipeStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:recipes,id',
            'status' => 'required|in:0,1',
        ]);

        try {
            $recipe = Recipe::findOrFail($request->id);
            $recipe->status = $request->status;
            $recipe->save();

            return response()->json(['success' => true, 'message' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to update status.'], 500);
        }
    }

    /* ------------------------------------------ update recipe of the week ---------------------------------- */
    public function updateRecipeOftheWeekStatus(Request $request)
    {
        try {
            $upateRecipeStatus = Recipe::findOrFail($request->recipe_id);
            $upateRecipeStatus->recipe_of_the_week = $request->status == 'Active' ? 0 : 1;
            $upateRecipeStatus->save();

            return response()->json(['success' => true, 'status' => $upateRecipeStatus->recipe_of_the_week, 'message' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }
    
    /* ---- Popular Recipe Listing ------*/
    public function popularRecipe(Request $request)
    {
        $common = Helper::getCommonData('Popular Recipes');
        $recipes = Recipe::with(['recipeIngredient', 'recipeSteps'])
                         ->where('status',1)
                         ->latest()
                         ->get();

        return view('admin.recipes.popular_recipes', compact('common', 'recipes'));
    }
    
    /* ----- View recipe of the Month ----- */
    public function RecipeOfTheMonth(Request $request)
    {
        $common = Helper::getCommonData('Recipe of The Month');
        $recipes = Recipe::with(['recipeIngredient', 'recipeSteps'])
                         ->where('recipe_of_the_week',1)
                         ->latest()
                         ->get();
                         
        return view('admin.recipes.recipe_of_the_month', compact('common', 'recipes'));
    }
    
    public function getCurrentRecipeOfTheWeek()
    {
        $currentRecipe = Recipe::where('recipe_of_the_week', 1)->first();
        return response()->json($currentRecipe);
    }

}
