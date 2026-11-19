<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\{Validator,Auth,DB};
use App\Models\{
    Recipe,
    Story,
    AddToFavorite,
    FollowRequest,
    FriendRequest,
    RecipeIngredient,
    RecipeStep,
    RecipeTool,
    StoryImage,
    RecipeLike
};

class ParentController extends Controller
{
    /**
     * add stories api
     */
    public function addStories(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'description' => 'required',
                'image' => 'required|mimes:jpeg,jpg,png|max:5120',
                'images' => 'required|array',
                'images.*' => 'mimes:jpeg,jpg,png|max:5120',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            // retrieve parent id
            $user = auth::user();

            $story = Story::create([
                'user_id' => $user->role === 'parent' ? $user->id : $user->parent_id,
                'kid_id' => $user->role === 'kid' ? $user->id : null,
                'role' => $user->role === 'parent' ? 'parent' : 'kid',
                'title' => $request->title,
                'description' => $request->description,
                'image' => $this->handleFileUpload($request, 'image', 'story_images', $existingPath = null),
                'video_link' => isset($request->video_link)?$request->video_link:'',
            ]);

            if ($request->has('images')) {
                foreach ($request->images as $imageFile) {
                    $imagePath = $imageFile->isValid()
                        ? Helper::handleUploadedFile($imageFile, 'story_images', $existingPath = null)
                        : null;

                    // Insert each image into the StoryImage table
                    StoryImage::create([
                        'story_id' => $story->id,
                        'images' => $imagePath,
                    ]);
                }
            }

            $story->load('storyImages');
            
            return Helper::responseFunction(true, 200, 'Stories created successfully!', $story, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => $e->getMessage()], 422);
        }
    }
    
    /**
     * edit stories api
    */ 
    public function editStories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'story_id'    => 'required|exists:stories,id',
            'title'       => 'nullable',
            'description' => 'nullable',
            'image'       => 'nullable|mimes:jpeg,jpg,png|max:5120',
            'images'      => 'nullable|array',
            'images.*'    => 'nullable|mimes:jpeg,jpg,png|max:5120',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        // retrieve user
        $user = auth()->user();
        
        $story = Story::where('id', $request->story_id)
        ->when($user->role == 'parent', function ($query) use ($user) {
            return $query->where('user_id', $user->id)
                         ->whereNull('kid_id');
        })
        ->when($user->role == 'kid', function ($query) use ($user) {
            return $query->where('kid_id', $user->id);
        })
        ->first();
    
        if(!$story) {
            return Helper::responseFunction(true, 200, 'You cannot update this story!', null, 200);
        }
        
        $story->update([
           'title' => $request->filled('title') ? $request->title : $story->title,
           'video_link' => isset($request->video_link)?$request->video_link:'',
           'description' => $request->filled('description') ? $request->description : $story->description,
           'image' => $request->hasFile('image') ? $this->handleFileUpload($request, 'image', 'story_images', $existingPath = null) : $story->image,
        ]);
        
        if ($request->hasFile('images')) {
            StoryImage::where('story_id', $request->story_id)->delete();
            
            foreach ($request->images as $imageFile) {
                $imagePath = $imageFile->isValid()
                    ? Helper::handleUploadedFile($imageFile, 'story_images', $existingPath = null)
                    : null;

                // Insert each image into the StoryImage table
                StoryImage::create([
                    'story_id' => $request->story_id,
                    'images' => $imagePath,
                ]);
            }
        }
        
        $story->load('storyImages');
        return Helper::responseFunction(true, 200, 'Story updated successfully!', $story, 200);
        
    }

    /**
     *  our story listing api
     */
    public function storyListing(Request $request)
    {
        try {
            $user = auth::user();

            $storyList = Story::with('storyImages')
                ->where(function ($query) use ($user) {
                    if ($user->role === 'parent') {
                        $query->where('user_id', $user->id)
                            ->whereStatus(0);
                    } elseif ($user->role === 'kid') {
                        $query->where('kid_id', $user->id)
                            ->where('user_id', $user->parent_id)
                            ->whereStatus(0);
                    }
                })->paginate(10);
                
            $storyList->getCollection()->transform(function ($story) {
                $storyImageVariants = Helper::getImageVariants($story->image);
                $story->image_medium = $storyImageVariants['medium'];
                $story->image_thumb = $storyImageVariants['thumb'];
                
                $story->storyImages->transform(function ($image) {
                    $variants = Helper::getImageVariants($image->images);
                    $image->images_medium = $variants['medium'];
                    $image->images_thumb = $variants['thumb'];

                    return $image;
                });

                return $story;
            });

            $isBestfromtheChamps = Story::with('storyImages')
                ->where(function ($query) use ($user) {

                    if ($user->role === 'parent') {
                        $query->where('user_id', $user->id)
                            ->where('status', 1);
                    } elseif ($user->role === 'kid') {
                        $query->where('kid_id', $user->id)
                            ->where('user_id', $user->parent_id)
                            ->where('status', 1);
                    }
                })->get();
                
            $isBestfromtheChamps->transform(function ($story) {
                $storyImageVariants = Helper::getImageVariants($story->image);
                $story->image_medium = $storyImageVariants['medium'];
                $story->image_thumb = $storyImageVariants['thumb'];
                
                $story->storyImages->transform(function ($image) {
                    $variants = Helper::getImageVariants($image->images);
                    $image->images_medium = $variants['medium'];
                    $image->images_thumb = $variants['thumb'];

                    return $image;
                });

                return $story;
            });

            if ($storyList->isEmpty()) {
                return response()->json(['status' => true, 'status_code' => 200, 'message' => 'Stories not found!', 'data' => ['total_page' => $storyList->lastPage(), 'items' => $storyList->items(), 'best_from_the_champs' => $isBestfromtheChamps ?? []]], 200);
            }

            return response()->json(['status' => true, 'status_code' => 200, 'data' => ['total_page' => $storyList->lastPage(), 'items' => $storyList->items(), 'best_from_the_champs' => $isBestfromtheChamps ?? []], 'message' => 'Stories retrieve successfully!'], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'An error occurred: '.$e->getMessage()], 422);
        }
    }

    /**
     * our story detail api
     */
    public function storyDetail(Request $request)
    {
        try {
            $user = auth::user();

            $storyDetail = Story::with('storyImages')->where('id', $request->story_id)->first();

            if ($storyDetail && ! \App\Support\ContentAccess::canViewStory($user, $storyDetail)) {
                return Helper::responseFunction(false, 404, 'Story not found!', null, 404);
            }

            if (! empty($storyDetail)) {
                $storyDetail->video_link = !empty($storyDetail->video_link)?$storyDetail->video_link:'';
                
                $variants = Helper::getImageVariants($storyDetail->image);
                $storyDetail->image_medium = $variants['medium'];
                $storyDetail->image_thumb = $variants['thumb'];
                
                $storyDetail->storyImages->transform(function ($image) {
                    $imageVariants = Helper::getImageVariants($image->images);
                    $image->images_medium = $imageVariants['medium'];
                    $image->images_thumb = $imageVariants['thumb'];

                    return $image;
                });
            }

            if (empty($storyDetail)) {
                return Helper::responseFunction(true, 200, 'Story detail not found!', null, 404);
            }

            return Helper::responseFunction(true, 200, 'Stories detail retrieve successfully!', $storyDetail, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'An error occurred: '.$e->getMessage()], 422);
        }
    }

    /**
     * add recipe api
     */
    public function addRecipe(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipe_name' => 'required',
                'banner_image' => 'required|mimes:jpeg,jpg,png|max:5120',
                'recipe_tools' => 'required',
                'ingredients' => 'required',
                'recipe_steps' => 'required',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $user = auth::user();

            $recipe = Recipe::create([
                'user_id' => $user->role === 'parent' ? $user->id : $user->parent_id,
                'kid_id' => $user->role === 'kid' ? $user->id : null,
                'role' => $user->role === 'parent' ? 'parent' : 'kid',
                'name' => $request->recipe_name,
                'banner_image' => $this->handleFileUpload($request, 'banner_image', 'recipes', $existingPath = null),
                'recipe_tools' => $request->recipe_tools,
                'video_link' => isset($request->video_link)?$request->video_link:'',
            ]);

            // Add ingredients and steps
            $this->addRecipeIngredients($recipe->id, $request->ingredients);
            $this->addRecipeSteps($recipe->id, $request->recipe_steps);

            $toolIds = explode(',', $recipe->recipe_tools);

            $recipeTools = RecipeTool::whereIn('id', $toolIds)->get();

            if ($recipeTools->isNotEmpty()) {
                $recipe->recipe_tools = $recipeTools;
            }

            // load relationship
            $recipe->load('recipeIngredient', 'recipeSteps');

            return Helper::responseFunction(true, 200, 'Recipe added successfully!', $recipe, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'An error occurred: '.$e->getMessage()], 422);
        }
    }

    /**
     * Add ingredients to the recipe.
     */
    private function addRecipeIngredients($recipeId, $ingredients)
    {
        $Ingredients = explode(',', $ingredients);
        foreach ($Ingredients as $val) {
            RecipeIngredient::create([
                'recipe_id' => $recipeId,
                'name' => trim($val),
            ]);
        }
    }

    /**
     * Add steps to the recipe.
     */
    private function addRecipeSteps($recipeId, $recipeSteps)
    {
        $RecipeSteps = explode(',', $recipeSteps);
        foreach ($RecipeSteps as $step) {
            RecipeStep::create([
                'recipe_id' => $recipeId,
                'name' => trim($step),
            ]);
        }
    }
    
    /*
    * edit recipe
    */
    public function editRecipe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipe_id'    => 'required|exists:recipes,id',
            'recipe_name'  => 'nullable',
            'banner_image' => 'nullable|mimes:jpeg,jpg,png|max:5120',
            'recipe_tools' => 'nullable',
            'ingredients'  => 'nullable',
            'recipe_steps' => 'nullable',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $user = auth::user();
        
        $recipe = Recipe::find($request->recipe_id);
        if(!$recipe) {
             return Helper::responseFunction(false, 404, 'Recipes not found!', null, 404);
        }
        
        $recipe->update([
            'name' => $request->filled('recipe_name') ? $request->recipe_name : $recipe->name,
            'video_link' => isset($request->video_link)?$request->video_link:'',
            'banner_image' => $request->hasFile('banner_image') ? $this->handleFileUpload($request, 'banner_image', 'recipes', $existingPath = null) : $recipe->banner_image,
            'recipe_tools' => $request->recipe_tools ? $request->recipe_tools : $recipe->recipe_tools
        ]);
        
        
        $toolIds = explode(',', $recipe->recipe_tools);

        $recipeTools = RecipeTool::whereIn('id', $toolIds)->get();

        if ($recipeTools->isNotEmpty()) {
            $recipe->recipe_tools = $recipeTools;
        }
        
        if($request->filled('ingredients')){
            RecipeIngredient::where('recipe_id', $request->recipe_id)->delete();
            $Ingredients = explode(',', $request->ingredients);
            
            foreach ($Ingredients as $val) {
                $ingredientName = trim($val);
                
                RecipeIngredient::create([
                        'recipe_id' => $request->recipe_id,
                        'name' => $ingredientName,
                    ]
                );
            }
        }
        
        if($request->filled('recipe_steps')){
            $RecipeSteps = explode(',', $request->recipe_steps);
            RecipeStep::where('recipe_id', $request->recipe_id)->delete();
            
            foreach ($RecipeSteps as $step) {
                RecipeStep::create([
                    'recipe_id' => $request->recipe_id,
                    'name' => trim($step),
                ]);
            }
        }
        
        // load relationship
        $recipe->load('recipeIngredient', 'recipeSteps');
        
        return Helper::responseFunction(true, 200, 'Recipe updated successfully!', $recipe, 200);
    }

    /**
     *  recipe listing api
     */
    public function recipeListing(Request $request)
    {
        try {
            $user = auth::user();
        
            // Base query for recipes
            $query = Recipe::with(['recipeIngredient', 'recipeSteps']);

            // Apply filters based on user role
            if ($user->role === 'parent') {
                $query->where('user_id', $user->id)
                      ->whereNull('kid_id');
            } elseif ($user->role === 'kid') {
                $query->where('kid_id', $user->id)
                    ->where('user_id', $user->parent_id);
            }

            if ($request->type == 'all') {
                $query = Recipe::with(['recipeIngredient', 'recipeSteps']);
            }

            $recipeList = $query->paginate(10);

            if ($recipeList->isNotEmpty()) {
                $recipes = $recipeList->getCollection();
                
                $toolIds = $recipes->pluck('recipe_tools')
                    ->filter()
                    ->flatMap(fn ($tools) => explode(',', $tools))
                    ->map(fn ($id) => trim((string) $id))
                    ->filter()
                    ->unique()
                    ->values();

                $recipeToolsById = RecipeTool::whereIn('id', $toolIds)->get()->keyBy('id');

                $favoriteRecipeIds = AddToFavorite::where('user_id', $user->id)
                    ->whereIn('recipe_id', $recipes->pluck('id'))
                    ->pluck('recipe_id')
                    ->map(fn ($id) => (string) $id)
                    ->all();
                $favoriteMap = array_flip($favoriteRecipeIds);

                $recipes->transform(function ($recipe) use ($recipeToolsById, $favoriteMap) {
                    $toolCollection = collect(explode(',', (string) $recipe->recipe_tools))
                        ->map(fn ($id) => trim((string) $id))
                        ->filter()
                        ->map(fn ($id) => $recipeToolsById->get($id))
                        ->filter()
                        ->values();

                    $recipe->recipe_tools = $toolCollection->isNotEmpty() ? $toolCollection : [];
                    $recipe->is_favourite = isset($favoriteMap[(string) $recipe->id]) ? 'Yes' : 'No';
                    
                    $variants = Helper::getImageVariants($recipe->banner_image);
                    $recipe->banner_image_medium = $variants['medium'];
                    $recipe->banner_image_thumb = $variants['thumb'];

                    return $recipe;
                });

                $recipeList->setCollection($recipes);
            }

            if ($recipeList->isEmpty()) {
                return Helper::responseFunction(true, 200, 'Recipes not found!', ['total_page' => $recipeList->lastPage(), 'items' => $recipeList->items()], 404);
            }

            return Helper::responseFunction(true, 200, 'Recipes retrieve successfully!', ['total_page' => $recipeList->lastPage(), 'items' => $recipeList->items()], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'An error occurred: '.$e->getMessage()], 422);
        }

    }

    /**
     * recipe detail api
    */
    public function recipeDetail(Request $request)
    {
        try{
            $user = auth::user();
            
            $recipeId = $request->recipe_id;
    
            if (empty($recipeId)) {
                return Helper::responseFunction(false, 200, 'Recipe id is required', null, 400);
            }
    
            $recipeDetail = Recipe::with(['recipeIngredient', 'recipeSteps'])->find($recipeId);
            if (! $recipeDetail) {
                return Helper::responseFunction(false, 200, 'Recipe detail not found!', null, 404);
            }

            if (! \App\Support\ContentAccess::canViewRecipe($user, $recipeDetail)) {
                return Helper::responseFunction(false, 404, 'Recipe detail not found!', null, 404);
            }
            
            $recipeDetail->video_link = !empty($recipeDetail->video_link) ? $recipeDetail->video_link:'';
            
            $bannerVariants = Helper::getImageVariants($recipeDetail->banner_image);
            $recipeDetail->banner_image_medium = $bannerVariants['medium'];
            $recipeDetail->banner_image_thumb = $bannerVariants['thumb'];
            
            // RecipeLike count 
            $reactionCounts = RecipeLike::select('status', DB::raw('COUNT(*) as total'))
                ->where('recipe_id', $recipeId)
                ->groupBy('status')
                ->pluck('total', 'status');
            
            $recipeDetail->likeCount = (string) ($reactionCounts['like'] ?? 0);
            $recipeDetail->heartCount = (string) ($reactionCounts['heart'] ?? 0);
            $recipeDetail->starCount = (string) ($reactionCounts['star'] ?? 0);
            $recipeDetail->emojiCount = (string) ($reactionCounts['emoji'] ?? 0);
            $recipeDetail->batchCount = (string) ($reactionCounts['batch'] ?? 0);
            $recipeDetail->cookCount = (string) ($reactionCounts['cook'] ?? 0);
    
            $statusMap = [
                '0' => 'pending',
                '1' => 'accepted',
                '2' => 'declined',
            ];
    
            $friendRequestStatus = 'request not sent';
    
            if ($recipeDetail->role === 'parent' && $recipeDetail->recipeAddedByParent) {
                
                $recipeDetail->recipe_added_by = $recipeDetail->recipeAddedByParent;
                unset($recipeDetail->recipeAddedByParent);
                
                $recipeDetail->recipe_added_by->name = trim(
                    $recipeDetail->recipe_added_by->first_name.' '.$recipeDetail->recipe_added_by->last_name
                );
    
                $recipeDetail->recipe_added_by->day = $recipeDetail->recipe_added_by->day ?? '';
                $recipeDetail->recipe_added_by->month = $recipeDetail->recipe_added_by->month ?? '';
                $recipeDetail->recipe_added_by->year = $recipeDetail->recipe_added_by->year ?? '';
                $recipeDetail->recipe_added_by->grade = $recipeDetail->recipe_added_by->grade ?? '';
                
                // show friend req status
                // $followReqStatus = $this->setFollowRequestStatus($user, $recipeDetail, $recipeDetail->recipe_added_by);
                
                $followReqStatus = FollowRequest::where(function($query) use($user, $recipeDetail) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $recipeDetail->recipe_added_by->id)
                          ->where('follow_req_sent_to', $recipeDetail->recipe_added_by->role);
                        //   ->where('follow_req_sent_by', 'parent');
                    // $query->where('receiver_id', $recipeDetail->recipe_added_by->id)
                    //       ->where('follow_req_sent_to', $recipeDetail->recipe_added_by->role);
                })
                ->exists();
            
                $recipeDetail->recipe_added_by->follow_request_status = $followReqStatus ? 'unfollow' : 'follow';
    
            } elseif ($recipeDetail->role === 'kid' && $recipeDetail->recipeAddedByChild) {
                
                // set follow request status
                // $followReqStatus = $this->setFollowRequestStatus($user, $recipeDetail, $recipeDetail->recipeAddedByChild);
                
                $followReqStatus = FollowRequest::where(function($query) use($user, $recipeDetail) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $recipeDetail->recipeAddedByChild->id)
                          ->where('follow_req_sent_to', $recipeDetail->recipeAddedByChild->role);
                        //   ->where('follow_req_sent_by', 'parent');
                    // $query->where('receiver_id', $recipeDetail->recipe_added_by->id)
                    //       ->where('follow_req_sent_to', $recipeDetail->recipe_added_by->role);
                })
                ->exists();
                
                $recipeDetail->recipeAddedByChild->follow_request_status = $followReqStatus ? 'unfollow' : 'follow';
               
                // create alias relationship name
                $recipeDetail->recipe_added_by = $recipeDetail->recipeAddedByChild;
                unset($recipeDetail->recipeAddedByChild);
            }
    
            /* ----- retrieve recipe tool ---- */
            $toolIds = explode(',', $recipeDetail->recipe_tools);
            $recipeTools = RecipeTool::whereIn('id', $toolIds)->get();
    
            if ($recipeTools->isNotEmpty()) {
                $recipeDetail->recipe_tools = $recipeTools;
            }
    
            return Helper::responseFunction(true, 200, 'Recipe detail retrieve successfully!', $recipeDetail, 200);        
        }
        catch(\Exception $e){
            return response()->json(['status'=>false,'status_code'=>422,'message'=> 'An error occurred: ' . $e->getMessage()],422);
        }
    }
    
 
    // friend request status
    private function getFriendRequestStatus($userId, $receiverId, $requestSentTo, $statusMap)
    {
        $status = FriendRequest::where([
            ['sender_id', $userId],
            ['receiver_id', $receiverId],
            ['request_sent_to', $requestSentTo],
        ])->value('status');

        return $statusMap[$status] ?? 'request not sent';
    }

    /**
     * recipe tools listing api
     */
    public function recipeTools(Request $request)
    {
        try {
            $recipeTool = RecipeTool::select('id', 'tool_name', 'tool_image')->get();
            if (! $recipeTool) {
                return Helper::responseFunction(true, 200, 'Recipe Tools not found!', null, 404); 
            }

            return Helper::responseFunction(true, 200, 'Recipe Tools retrieve successfully!', $recipeTool, 200); 
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'An error occurred: '.$e->getMessage()], 422);
        }

    }
    
    /*
    * like Recipe
    */
    public function likeRecipe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipe_id'    => 'required',
            'type' => 'required',  // type for manage kids or parent
            //'status'  => 'required', // status manage for like , emoji, heart etc.....
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }
        
        $user = auth::user();
        
        $recipe =  RecipeLike::updateOrInsert(
            [
                'user_id' => $user->id,
                'recipe_id' => $request->recipe_id,
                'type' => $request->type,
            ],
            [
                'status' => $request->status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        );

        
        return Helper::responseFunction(true, 200, 'Recipe Liked successfully!', [], 200);
    }

    /**
     * file upload function
     */
    private function handleFileUpload(Request $request, $fileKey, $storagePath, $existingPath = null)
    {
        return Helper::handleFileUpload($request, $fileKey, $storagePath, $existingPath);
    }
}
