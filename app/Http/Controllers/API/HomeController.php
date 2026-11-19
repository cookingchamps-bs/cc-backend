<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator,Auth};
use App\Models\{
    AddToFavorite,
    FollowRequest,
    FriendRequest,
    GrowingActivity,
    MonthlyChallenge,
    FcmToken,
    Kid,
    Notification,
    Recipe,
    RecipeTool,
    Story,
    StoryShare,
    User,
    Banner,
    Product,
    ProductCategory,
    WishList,
    AddToCart,
    Address,
    Subscription
};


class HomeController extends Controller
{
    /**
     * Classic home page API used by the Flutter app (full recipe relations).
     * See HomeControllerV1 for the lighter alternate payload — do not switch
     * mobile to v1/home without updating Flutter models.
     */
    public function home(Request $request)
    {
        try {
            $data = [];
            
            // get monthly challenge banner
           $data['monthly_challenge_banner'] = MonthlyChallenge::where('status', 1)
            ->select('id', 'banner_image') // add only needed columns
            ->first() ?? (object)[];
                    
            
            
            if (! empty($data['monthly_challenge_banner']->banner_image)) {
                $variants = Helper::getImageVariants($data['monthly_challenge_banner']->banner_image);
                $data['monthly_challenge_banner']->banner_image_medium = $variants['medium'];
                $data['monthly_challenge_banner']->banner_image_thumb = $variants['thumb'];
            }
            
            // Retrieve recipe of the week
            $recipe = Recipe::with(['recipeIngredient', 'recipeSteps'])
                ->where('recipe_of_the_week', 1)
                ->first();
                
            if(!$recipe){
                $recipe = Recipe::with(['recipeIngredient', 'recipeSteps'])
                            ->inRandomOrder()
                            ->first();
            }
                
            $allToolIds = collect();
            if ($recipe && ! empty($recipe->recipe_tools)) {
                $allToolIds = $allToolIds->merge(explode(',', $recipe->recipe_tools));
            }
            
            $data['recipe_of_the_week'] = $recipe ?? (object)[];
            

            // get growing Activity
            $growingActivity = GrowingActivity::where('status',1)->select('id','title','image','status')->first();
            
            if ($growingActivity && $growingActivity->description) {
                $growingActivity->description = HtmlSanitizer::richText($growingActivity->description);
            }
            if ($growingActivity && ! empty($growingActivity->image)) {
                $variants = Helper::getImageVariants($growingActivity->image);
                $growingActivity->image_medium = $variants['medium'];
                $growingActivity->image_thumb = $variants['thumb'];
            }

            $data['growing_activity'] = $growingActivity ?? "";

            // Get popular recipes excluding the recipe of the week
            $popularRecipes = Recipe::with(['recipeIngredient', 'recipeSteps'])
                ->where('status', 1)
                ->where('recipe_of_the_week', '!=', 1)
                ->get();

            if ($popularRecipes->isNotEmpty()) {
                $allToolIds = $allToolIds->merge(
                    $popularRecipes->pluck('recipe_tools')
                        ->filter()
                        ->flatMap(fn ($tools) => explode(',', $tools))
                );
            }
            
            $allToolIds = $allToolIds->map(fn ($id) => trim((string) $id))
                ->filter()
                ->unique()
                ->values();
            
            $recipeToolsById = RecipeTool::whereIn('id', $allToolIds)->get()->keyBy('id');
            
            if ($recipe) {
                $toolCollection = collect(explode(',', (string) $recipe->recipe_tools))
                    ->map(fn ($id) => trim((string) $id))
                    ->filter()
                    ->map(fn ($id) => $recipeToolsById->get($id))
                    ->filter()
                    ->values();

                $recipe->recipe_tools = $toolCollection->isNotEmpty() ? $toolCollection : [];
                
                if (! empty($recipe->banner_image)) {
                    $variants = Helper::getImageVariants($recipe->banner_image);
                    $recipe->banner_image_medium = $variants['medium'];
                    $recipe->banner_image_thumb = $variants['thumb'];
                }
            }
            
            if ($popularRecipes->isNotEmpty()) {
                $popularRecipes->transform(function ($recipe) use ($recipeToolsById) {
                    $toolCollection = collect(explode(',', (string) $recipe->recipe_tools))
                        ->map(fn ($id) => trim((string) $id))
                        ->filter()
                        ->map(fn ($id) => $recipeToolsById->get($id))
                        ->filter()
                        ->values();

                    $recipe->recipe_tools = $toolCollection->isNotEmpty() ? $toolCollection : [];
                    
                    if (! empty($recipe->banner_image)) {
                        $variants = Helper::getImageVariants($recipe->banner_image);
                        $recipe->banner_image_medium = $variants['medium'];
                        $recipe->banner_image_thumb = $variants['thumb'];
                    }

                    return $recipe;
                });
            }
            
            $data['popular_recipes'] = $popularRecipes ?? [];
            
            // see all popular recipe
            if ($request->has('see_all') && $request->see_all === 'popular_recipe') {
                return Helper::responseFunction(true, 200, 'Popular recipes retrieved successfully!', ['popular_recipes'=> $popularRecipes], 200);
            }

            // Get featured stories
            $featuredStories = Story::where('is_featured', 1)->get();
            if ($featuredStories->isNotEmpty()) {
                $featuredStories->transform(function ($story) {
                    $variants = Helper::getImageVariants($story->image);
                    $story->image_medium = $variants['medium'];
                    $story->image_thumb = $variants['thumb'];
                    $story->makeHidden(['user_id', 'kid_id', 'role', 'deleted_at']);

                    return $story;
                });
            }
            
            // see all featured story
            if ($request->has('see_all') && $request->see_all === 'featured_stories') {
                return Helper::responseFunction(true, 200, 'Featured stories retrieved successfully!', ['featured_stories' => $featuredStories], 200);
            }
            
            $data['featured_stories'] = $featuredStories ?? [];

            return Helper::responseFunction(true, 200, 'Data retrieved successfully!', $data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving data.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Monthly challenge Detail
     */ 
    public function monthlyChallengeDetail(Request $request, $id)
    {
        $monthlyChallengeDetail = MonthlyChallenge::find($id);
        
        if(!$monthlyChallengeDetail){
            return Helper::responseFunction(false, 404, 'Challenge not found!', null, 404);
        }
        
        return Helper::responseFunction(true, 200, 'Challenge retrieved successfully!', $monthlyChallengeDetail, 200); 
    }
    
    
    /**
     *  Banner listing api
     */ 
    public function banners(Request $request)
    {
        $banners = Banner::first();
        
        if(!$banners){
            return Helper::responseFunction(false, 404, 'Banner not found!', null, 404);
        }
        
        return Helper::responseFunction(true, 200, 'Banners retrieved successfully!', $banners, 200); 
    }

    /**
     * add to favorites api
     */
    public function addtoFavorites(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'recipe_id' => 'required|exists:recipes,id',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $user = auth::user();

            $favorite = AddToFavorite::where('recipe_id', $request->recipe_id)
                        // ->where('user_id', $user->id)
                        ->first();

            if ($favorite) {
                $favorite->delete();

                return Helper::responseFunction(true, 200, 'Recipe removed from favorites successfully!', null, 200);
            }

            // Add to favorites if not already present
            AddToFavorite::create([
                'recipe_id' => $request->recipe_id,
                'user_id' => $user->id,
                'kid_id' => null,
            ]);

            return Helper::responseFunction(true, 200, 'Recipe added to favorites successfully!', null, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }

    /**
     * Favorites listing api
     */
    public function favoritesListing(Request $request)
    {
        try {
            $user = auth::user();
            
            $favorites = AddToFavorite::with('recipe')->where('user_id', $user->id)
                ->paginate(10);
            
            $toolIds = $favorites->getCollection()
                ->pluck('recipe.recipe_tools')
                ->filter()
                ->flatMap(fn ($tools) => explode(',', $tools))
                ->map(fn ($id) => trim((string) $id))
                ->filter()
                ->unique()
                ->values();
            $recipeToolsById = RecipeTool::whereIn('id', $toolIds)->get()->keyBy('id');
        
            if ($favorites->isNotEmpty()) {
                foreach ($favorites as $val) {
                    if (empty($val->recipe)) {
                        continue;
                    }
                    
                    $recipeToolCollection = collect(explode(',', (string) $val->recipe->recipe_tools))
                        ->map(fn ($id) => trim((string) $id))
                        ->filter()
                        ->map(fn ($id) => $recipeToolsById->get($id))
                        ->filter()
                        ->values();

                    $val->recipe->recipe_tools = $recipeToolCollection->isNotEmpty() ? $recipeToolCollection : [];
                    
                    if (! empty($val->recipe->banner_image)) {
                        $variants = Helper::getImageVariants($val->recipe->banner_image);
                        $val->recipe->banner_image_medium = $variants['medium'];
                        $val->recipe->banner_image_thumb = $variants['thumb'];
                    }
                }
            }

            // Extract only the related recipe data
            $favorites->getCollection()->transform(function ($favorite) {
                return $favorite->recipe;
            });

            if ($favorites->isEmpty()) {
                return Helper::responseFunction(false, 200, 'Favorites list not found!', ['total_page' => $favorites->lastPage(), 'items' => $favorites->items()], 404);
            }

            return Helper::responseFunction(true, 200, 'Favorites list retrieve successfully!', ['total_page' => $favorites->lastPage(), 'items' => $favorites->items()], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }

    /**
     * send friend request api
     */
    public function sendFriendRequest(Request $request)
    {
        try {
            $authUser = auth()->user();
            $senderId = $authUser->id;
            $requestSentBy = $authUser instanceof \App\Models\Kid ? 'kid' : 'parent';

            $validator = Validator::make($request->all(), [
                'receiver_id' => 'required',
                'request_sent_to' => 'required|in:parent,kid',
                'type' => 'nullable|in:cancel',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            if ((int) $request->receiver_id === (int) $senderId) {
                return Helper::responseFunction(false, 422, 'Cannot send a friend request to yourself.', null, 422);
            }

            if ($request->has('type') && ! empty($request->type)) {
                $cancelReq = FriendRequest::where([
                    'sender_id' => $senderId,
                    'receiver_id' => $request->receiver_id,
                    'request_sent_by' => $requestSentBy,
                    'request_sent_to' => $request->request_sent_to,
                ])->first();

                if (! $cancelReq) {
                    return Helper::responseFunction(false, 200, 'Friend request not found!', null, 200);
                }

                $cancelReq->delete();

                return Helper::responseFunction(true, 200, 'Friend request cancelled successfully!', null, 200);
            }
            
            $friendReq = FriendRequest::where([
                'sender_id' => $senderId,
                'receiver_id' => $request->receiver_id,
                'request_sent_by' => $requestSentBy,
                'request_sent_to' => $request->request_sent_to,
            ])->get();

            if ($friendReq->isNotEmpty()) {
                foreach ($friendReq as $val) {
                    $val->delete();
                }
            }

            $friendReq = FriendRequest::create([
                'sender_id' => $senderId,
                'receiver_id' => $request->receiver_id,
                'request_sent_by' => $requestSentBy,
                'request_sent_to' => $request->request_sent_to,
            ]);

            return Helper::responseFunction(true, 200, 'Friend request sent successfully!', $friendReq, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }
    
    /**
     * send follow request api
     */ 
    public function sendFollowRequest(Request $request)
    {
        $authUser = auth()->user();
        $senderId = $authUser->id;
        $followReqSentBy = $authUser instanceof \App\Models\Kid ? 'kid' : 'parent';

        $validator = Validator::make($request->all(), [
            'receiver_id' => 'required',
            'follow_req_sent_to' => 'required|in:parent,kid',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $validatedData = [
            'sender_id' => $senderId,
            'receiver_id' => $request->receiver_id,
            'follow_req_sent_by' => $followReqSentBy,
            'follow_req_sent_to' => $request->follow_req_sent_to,
        ];

        $followRequest = FollowRequest::where($validatedData)->first();
        
        if($followRequest){
            $followRequest->delete();
            return Helper::responseFunction(true, 200, 'UnFollow successfully!', null, 200);
        }
        
        $followRequest = FollowRequest::create($validatedData);

        return Helper::responseFunction(true, 200, 'Followed successfully!', $followRequest, 200);
    }
    
    /**
     * follow request listing
     */ 
    public function followReqListing(Request $request)
    {
        $user = auth()->user();
        // return $user;
        
        $data = [];
        $statusMap = [
            '0' => 'pending',
            '1' => 'accepted',
            '2' => 'declined',
        ];
        
        /* ---- get followers list ----- */
        $getfollowersList = FollowRequest::where('receiver_id', $user->id)
                ->where('follow_req_sent_to', $user->role)
                ->get()
                ->map(function ($follower) {
                    if ($follower->follow_req_sent_by == 'parent') {
                        return [
                            'id' => $follower->id,
                            'sender_id' => $follower->sender_id,
                            'receiver_id' => $follower->receiver_id,
                            'follow_req_sent_by' => $follower->follow_req_sent_by,
                            'follow_req_sent_to' => $follower->follow_req_sent_to,
                            'created_at' => $follower->created_at,
                            'updated_at' => $follower->updated_at,
                            'user_details' => [
                                'id' => $follower->parentSenderDetails->id,
                                'name' => $follower->parentSenderDetails->first_name . ' ' . $follower->parentSenderDetails->last_name,
                                'role' => $follower->parentSenderDetails->role,
                                'email' => $follower->parentSenderDetails->email,
                                'image' => $follower->parentSenderDetails->image,
                                'day' => '',  
                                'month' => '',
                                'year' => '',
                                'grade' => '',
                            ]
                        ];
                    } else if ($follower->follow_req_sent_by == 'kid') {
                        return [
                            'id' => $follower->id,
                            'sender_id' => $follower->sender_id,
                            'receiver_id' => $follower->receiver_id,
                            'follow_req_sent_by' => $follower->follow_req_sent_by,
                            'follow_req_sent_to' => $follower->follow_req_sent_to,
                            'created_at' => $follower->created_at,
                            'updated_at' => $follower->updated_at,
                            'user_details' => $follower->childSenderDetails
                        ];
                    }
                });
                
        if ($getfollowersList->isNotEmpty()) {
            // Fetch friend request statuses with proper bidirectional key formation
            $friendRequestStatuses = FriendRequest::where(function ($query) use ($getfollowersList) {
                foreach ($getfollowersList as $val) {
                    $query->orWhere(function ($subquery) use ($val) {
                        $subquery->where('sender_id', $val['sender_id'])
                                 ->where('receiver_id', $val['receiver_id'])
                                 ->where('request_sent_by', $val['follow_req_sent_by'])
                                 ->where('request_sent_to', $val['follow_req_sent_to']);
                    })->orWhere(function ($subquery) use ($val) {
                        $subquery->where('sender_id', $val['receiver_id'])
                                 ->where('receiver_id', $val['sender_id'])
                                 ->where('request_sent_by', $val['follow_req_sent_to'])
                                 ->where('request_sent_to', $val['follow_req_sent_by']);
                    });
                }
            })->get(['sender_id', 'receiver_id', 'request_sent_by', 'request_sent_to', 'status'])
              ->mapWithKeys(function ($item) {
                  // Normalize the key so sender-receiver pairs match both ways
                  $key1 = "{$item->sender_id}_{$item->receiver_id}_{$item->request_sent_by}_{$item->request_sent_to}";
                  $key2 = "{$item->receiver_id}_{$item->sender_id}_{$item->request_sent_to}_{$item->request_sent_by}";
                  return [$key1 => $item->status, $key2 => $item->status]; // Store both directions
              });
    
            // Assign friend request status to each follower
            $getfollowersList = $getfollowersList->map(function ($follower) use ($friendRequestStatuses, $statusMap) {
                $key = "{$follower['sender_id']}_{$follower['receiver_id']}_{$follower['follow_req_sent_by']}_{$follower['follow_req_sent_to']}";
                $status = $friendRequestStatuses[$key] ?? null;
                $follower['user_details']['friend_request_status'] = $statusMap[$status] ?? 'request not sent';
                return $follower;
            });
        }

        $data['my_followers'] = $getfollowersList;
        
        /* ---- get following list ----- */     
        $getIamfollowingList = FollowRequest::where('sender_id', $user->id)
                ->where('follow_req_sent_by', $user->role)
                ->get()
                ->map(function ($following) {
                    if ($following->follow_req_sent_to == 'parent') {
                        return [
                            'id' => $following->id,
                            'sender_id' => $following->sender_id,
                            'receiver_id' => $following->receiver_id,
                            'follow_req_sent_by' => $following->follow_req_sent_by,
                            'follow_req_sent_to' => $following->follow_req_sent_to,
                            'created_at' => $following->created_at,
                            'updated_at' => $following->updated_at,
                            'user_details' => [
                                'id' => $following->parentReceiverDetails->id,
                                'name' => $following->parentReceiverDetails->first_name . ' ' . $following->parentReceiverDetails->last_name,
                                'role' => $following->parentReceiverDetails->role,
                                'email' => $following->parentReceiverDetails->email,
                                'image' => $following->parentReceiverDetails->image,
                                'day' => '',  
                                'month' => '',
                                'year' => '',
                                'grade' => '',
                            ]
                        ];
                    } else if ($following->follow_req_sent_to == 'kid') {
                        return [
                            'id' => $following->id,
                            'sender_id' => $following->sender_id,
                            'receiver_id' => $following->receiver_id,
                            'follow_req_sent_by' => $following->follow_req_sent_by,
                            'follow_req_sent_to' => $following->follow_req_sent_to,
                            'created_at' => $following->created_at,
                            'updated_at' => $following->updated_at,
                            'user_details' => $following->childReceiverDetails
                        ];
                    }
                });
               
        if ($getIamfollowingList->isNotEmpty()) {
            // Fetch friend request statuses with proper bidirectional key formation
            $friendRequestStatuses = FriendRequest::where(function ($query) use ($getIamfollowingList) {
                foreach ($getIamfollowingList as $val) {
                    $query->orWhere(function ($subquery) use ($val) {
                        $subquery->where('sender_id', $val['sender_id'])
                                 ->where('receiver_id', $val['receiver_id'])
                                 ->where('request_sent_by', $val['follow_req_sent_by'])
                                 ->where('request_sent_to', $val['follow_req_sent_to']);
                    })->orWhere(function ($subquery) use ($val) {
                        $subquery->where('sender_id', $val['receiver_id'])
                                 ->where('receiver_id', $val['sender_id'])
                                 ->where('request_sent_by', $val['follow_req_sent_to'])
                                 ->where('request_sent_to', $val['follow_req_sent_by']);
                    });
                }
            })->get(['sender_id', 'receiver_id', 'request_sent_by', 'request_sent_to', 'status'])
              ->mapWithKeys(function ($item) {
                  // Normalize the key so sender-receiver pairs match both ways
                  $key1 = "{$item->sender_id}_{$item->receiver_id}_{$item->request_sent_by}_{$item->request_sent_to}";
                  $key2 = "{$item->receiver_id}_{$item->sender_id}_{$item->request_sent_to}_{$item->request_sent_by}";
                  return [$key1 => $item->status, $key2 => $item->status]; // Store both directions
              });
    
            // Assign friend request status to each follower
            $getIamfollowingList = $getIamfollowingList->map(function ($follower) use ($friendRequestStatuses, $statusMap) {
                $key = "{$follower['sender_id']}_{$follower['receiver_id']}_{$follower['follow_req_sent_by']}_{$follower['follow_req_sent_to']}";
                $status = $friendRequestStatuses[$key] ?? null;
                $follower['user_details']['friend_request_status'] = $statusMap[$status] ?? 'request not sent';
                return $follower;
            });
        }
                                
        $data['I_am_following'] = $getIamfollowingList;
                                
        return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $data, 200);
    }
    

    /**
     * share stories api
     */
    public function shareStories(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'story_id' => 'required',
            'friends_id' => 'required',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }

        $user = auth::user();
        $story = Story::find($request->story_id);
        if (! $story || ! $this->callerOwnsStory($user, $story)) {
            return Helper::responseFunction(false, 403, 'You can only share your own stories.', null, 403);
        }

        $friendsIds = explode(',', $request->friends_id);

        $friends = FriendRequest::whereIn('id', $friendsIds)
            ->whereStatus(1)
            ->get();

        foreach ($friends as $friend) {
            if ($friend->sender_id == $user->id) {
                // when user is the sender
                $storyShareTo = $friend->receiver_id;
                $storyShareToRole = $friend->request_sent_to;
            } elseif ($friend->receiver_id == $user->id && $friend->sender_id != $user->id) {
                // when user is the receiver
                $storyShareTo = $friend->sender_id;
                $storyShareToRole = $friend->request_sent_by;
            } else {
                continue;
            }

            $storyshare = StoryShare::updateOrCreate(
                ['story_id' => $request->story_id, 'story_share_by' => $user->id, 'story_share_to' => $storyShareTo],
                [
                    'story_id' => $request->story_id,
                    'story_share_by' => $user->id,
                    'role' => $user->role,
                    'story_share_to' => $storyShareTo,
                    'story_share_to_role' => $storyShareToRole,
                ]
            );

            $fcmToken = FcmToken::when($storyShareToRole === 'kid', function ($query) use ($storyShareTo) {
                    $query->where('child_id', $storyShareTo)
                        ->whereRole('kid');
                })
                ->when($storyShareToRole === 'parent', function ($query) use ($storyShareTo) {
                    $query->where('user_id', $storyShareTo)
                        ->whereRole('parent');
                })
                ->pluck('fcm_token')
                ->toArray();

            $msg = [
                'title' => 'Cooking Champs',
                'body' => 'Share Story Detail!',
                'story_id' => (string) $request->story_id,
                'story_share_by' => (string) $user->id,
                'role' => $user->role,
                'story_share_to' => (string) $storyShareTo,
                'storyShareToRole' => $storyShareToRole,
                'type' => 'Story Detail',
                'isBackground' => 'true',
                'sound' => 'default',
                'content_available' => 'true',
                'click_action' => 'NOTIFICATION_CLICK',
                'status' => '0',
                'priority' => 'high',
                'timestamp' => '',
                'imageUrl' => '',
            ];

            if ($fcmToken) {
                Helper::StoryDetailPushNotification($fcmToken, $msg);
            }
        }

        $sharedStories = StoryShare::where('story_share_by', $user->id)
            ->where('story_id', $request->story_id)
            ->get();

        return Helper::responseFunction(true, 200, 'Story Details shared successfully!', $sharedStories, 200);
    }

    /**
     * friend request api
     */
    public function friendRequest(Request $request)
    {
        try {
            $user = auth::user();
            // return $user;
            
            if ($request->type === 'all') {
                return Helper::responseFunction(false, 410, 'Listing all users is no longer available. Use pending or accepted friend lists.', null, 410);
            }

            $status = $request->type === 'pending' ? 0 : 1;

            $friendRequest = FriendRequest::when($user->role == 'parent', function ($query) use ($user, $status) {
                $query->where(function ($subQuery) use ($user, $status) {
                    $subQuery->where('receiver_id', $user->id)
                        ->where('request_sent_to', $user->role)
                        ->where('status', $status);
                });

                if ($status !== 0) {
                    $query->orWhere(function ($subQuery) use ($user, $status) {
                        $subQuery->where('sender_id', $user->id)
                            ->where('request_sent_by', $user->role)
                            ->where('status', $status);
                    });
                }
            })->when($user->role == 'kid', function ($query) use ($user, $status) {
                $query->where(function ($subQuery) use ($user, $status) {
                    $subQuery->where('receiver_id', $user->id)
                        ->where('request_sent_to', $user->role)
                        ->where('status', $status);
                });

                if ($status !== 0) {
                    $query->orWhere(function ($subQuery) use ($user, $status) {
                        $subQuery->where('sender_id', $user->id)
                            ->where('request_sent_by', 'kid')
                            ->where('status', $status);
                    });

                }
            })->get();

            if ($friendRequest->isEmpty()) {
                return Helper::responseFunction(true, 200, 'Friend request not found!', null, 200);
            }

            foreach ($friendRequest as $val) {
                if ($val->receiver_id == $user->id) {
                    // User is the receiver
                    if ($val->request_sent_by === 'kid') {
                        $val->sender_detail = $val->childSenderDetail; // Sender is a kid
                        unset($val->childSenderDetail);
                    } elseif ($val->request_sent_by === 'parent') {
                        $val->parentSenderDetail->name = trim(
                            $val->parentSenderDetail->first_name.' '.$val->parentSenderDetail->last_name
                        );
                        $val->parentSenderDetail->day = $val->parentSenderDetail->day ?? '';
                        $val->parentSenderDetail->month = $val->parentSenderDetail->month ?? '';
                        $val->parentSenderDetail->year = $val->parentSenderDetail->year ?? '';
                        $val->parentSenderDetail->grade = $val->parentSenderDetail->grade ?? '';
                        $val->sender_detail = $val->parentSenderDetail;
                        unset($val->parentSenderDetail);
                    }
                } elseif ($val->sender_id == $user->id) {
                    // User is the sender
                    if ($val->request_sent_to === 'kid') {
                        $val->sender_detail = $val->childReceiverDetail; // Receiver is a kid
                        unset($val->childReceiverDetail);
                    } elseif ($val->request_sent_to === 'parent') {
                        $val->parentReceiverDetail->name = trim(
                            $val->parentReceiverDetail->first_name.' '.$val->parentReceiverDetail->last_name
                        );
                        $val->parentReceiverDetail->day = $val->parentReceiverDetail->day ?? '';
                        $val->parentReceiverDetail->month = $val->parentReceiverDetail->month ?? '';
                        $val->parentReceiverDetail->year = $val->parentReceiverDetail->year ?? '';
                        $val->parentReceiverDetail->grade = $val->parentReceiverDetail->grade ?? '';
                        $val->sender_detail = $val->parentReceiverDetail;
                        unset($val->parentReceiverDetail);
                    }
                }
            }
            
            return Helper::responseFunction(true, 200, 'Friend request retrieve successfully!', $friendRequest, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }

    /**
     * show pending request count
     */
    public function pendingReqCount(Request $request)
    {
        try {
            $user = auth::user();
            $pendingRequestcount = FriendRequest::when($user->role == 'parent', function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('receiver_id', $user->id)
                        ->where('request_sent_to', $user->role)
                        ->where('status', 0);
                });
            })->when($user->role == 'kid', function ($query) use ($user) {
                $query->where(function ($subQuery) use ($user) {
                    $subQuery->where('receiver_id', $user->id)
                        ->where('request_sent_to', $user->role)
                        ->where('status', 0);
                });
            })->count();

            return response()->json(['status' => true, 'status_code' => 200, 'message' => 'Pending request count retrieve successfully!', 'pending_req_count' => $pendingRequestcount], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }

    /**
     * accept friend request api
     */
    public function acceptFriendRequest(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'friend_request_id' => 'required|exists:friend_requests,id',
                'accept' => 'required|in:yes,no',
            ]);

            if ($validator->fails()) {
                return Helper::validationResponse($validator, 200);
            }

            $user = auth::user();
        
            // retrieve request
            $friendRequest = FriendRequest::find($request->friend_request_id);
            if (! $friendRequest) {
                return Helper::responseFunction(false, 200, 'Friend request not found!', null, 404);
            }

            if (! $this->canRespondToFriendRequest($user, $friendRequest)) {
                return Helper::responseFunction(false, 403, 'Not authorized to respond to this friend request.', null, 403);
            }

            $status = $request->accept === 'yes' ? 1 : 2; // 1 for accept, 2 for decline
            $message = $status === 1 ? 'Friend request accepted!' : 'Friend request declined!';

            // retrieve fcmTokens
            $fcmToken = FcmToken::when($friendRequest->request_sent_by === 'kid', function ($query) use ($friendRequest) {
                $query->where('child_id', $friendRequest->sender_id)->whereRole('kid');
            })
            ->when($friendRequest->request_sent_by === 'parent', function ($query) use ($friendRequest) {
                $query->where('user_id', $friendRequest->sender_id)->whereRole('parent');
            })
            ->pluck('fcm_token');
            
            if ($fcmToken) {
                // $this->sendFriendRequestNotification($fcmToken, $user, $friendRequest, $message);
                $this->sendFriendRequestNotification($fcmToken, $user, $friendRequest, $message);
            }

            $friendRequest->update(['status' => $status]);

            $msg = $status === 1 ? 'Friend request accepted!' : 'Friend request declined!';

            return Helper::responseFunction(true, 200, $msg, $friendRequest, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }

    // send notification
    private function sendFriendRequestNotification($fcmToken, $user, $friendRequest, $message)
    {
        $msg = [
            'title' => 'Cooking Champs',
            'body' => $message,
            'user_id' => $user->id,
            'role' => $user->role,
            'sender_id' => $friendRequest->sender_id,
            'receiver_id' => $friendRequest->receiver_id,
            'request_sent_by' => $friendRequest->request_sent_by,
            'request_sent_to' => $friendRequest->request_sent_to,
            'type' => 'Accept Friend Request',
            'isBackground' => 'true',
            'sound' => 'default',
            'content_available' => 'true',
            'click_action' => 'NOTIFICATION_CLICK',
            'status' => '0',
            'priority' => 'high',
            'timestamp' => '',
            'imageUrl' => '',
        ];
        
        Notification::create([
            'user_id' => $user->role == 'parent' ? $user->id : $user->parent_id,
            'kid_id' => $user->role == 'kid' ? $user->id : null,
            'role' => $user->role,
            'type' => $msg['type'],
            'title' => $msg['title'],
            'message' => $msg['body'],
            'click_action' => $msg['click_action'],
            'sender_id' => $friendRequest->sender_id,
            'receiver_id' => $friendRequest->receiver_id,
            'request_sent_by' => $friendRequest->request_sent_by,
            'request_sent_to' => $friendRequest->request_sent_to,
            'status' => $msg['status'],
        ]);

        Helper::PushNotification($fcmToken, $msg);
        // echo "<pre>"; print_r($helper); die;
        
        \Log::info('Friend request notification send succcessfully!');
    }

    /**
     * notification list api
     */
    public function notificationListing(Request $request)
    {
        try {
            $user = auth::user();

            $notifications = Notification::with('userDetails')->when($user->role == 'kid', function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                    ->where('request_sent_to', 'kid');
            })->when($user->role === 'parent', function ($query) use ($user) {
                $query->where('receiver_id', $user->id)
                    ->where('request_sent_to', 'parent');
            })->paginate(10);

            if ($notifications->isEmpty()) {
                return Helper::responseFunction(false, 200, 'Notification list not found!', ['total_page' => $notifications->lastPage(), 'items' => $notifications->items()], 404);
            }

            $msg = 'Notification list retrieve successfully!';
            
            return Helper::responseFunction(true, 200, $msg, ['total_page' => $notifications->lastPage(), 'items' => $notifications->items()], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 500, 'message' => 'An error occurred: '.$e->getMessage()], 500);
        }
    }
    
    /**
     * delete notification api
     */ 
    public function deleteNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_id' => 'required|exists:notifications,id',
        ]);

        if ($validator->fails()) {
            return Helper::validationResponse($validator, 200);
        }
        
        $user = auth()->user();
        $notifications = Notification::find($request->notification_id);
        if (! $notifications) {
            return Helper::responseFunction(false, 404, 'Notificaton not found!', '', 404);
        }

        $ownerId = $user->role === 'kid' ? $user->id : $user->id;
        $isOwner = ($user->role === 'kid' && (int) $notifications->kid_id === (int) $ownerId)
            || ($user->role === 'parent' && (int) $notifications->user_id === (int) $user->id);
        if (! $isOwner) {
            return Helper::responseFunction(false, 404, 'Notificaton not found!', '', 404);
        }

        $notifications->delete();
        
        return Helper::responseFunction(true, 200, 'Notificaton deleted successfully!', "", 200);
        
    }
    
    /**
     * subscription list
     */ 
    public function subscriptionList(Request $request)
    {
        $user = auth()->user();
        
        $subscriptions = Subscription::with(['subscriptionDetails:id,subscription_id,title','userPurchaseSubscription' => function($query) use($user) {
            if($user->role === 'parent'){
                $query->where('user_id', $user->id)
                      ->where('role', 'parent');
            }else{
                $query->where('user_id', $user->parent_id)
                      ->where('kid_id', $user->id)
                      ->where('role', 'kid');
            }
        }])->get();
        
        
        $subscriptions = $subscriptions->map(function ($subscription) {
            $subscription->is_plan_purchase = $subscription->userPurchaseSubscription->isNotEmpty();
            
            unset($subscription->userPurchaseSubscription);
            return $subscription;
        });
        
        return Helper::responseFunction(true, 200, 'Subscription list retrieve successfully!', $subscriptions, 200);
    }
    
    
    
    public function getBannerDescription()
    {
        $data = MonthlyChallenge::where('status', 1)
            ->select('id', 'description')
            ->first();
    
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'No data found',
                'data' => (object)[]
            ], 404);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Banner description fetched successfully',
            'data' => $data
        ], 200);
    }
    
    
      public function getGrowingDescription()
    {
         $data = GrowingActivity::where('status',1)->select('id','description')->first();
    
        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'No data found',
                'data' => (object)[]
            ], 404);
        }
    
        return response()->json([
            'status' => true,
            'message' => 'Banner description fetched successfully',
            'data' => $data
        ], 200);
    }

    private function canRespondToFriendRequest($authUser, FriendRequest $friendRequest): bool
    {
        if ($friendRequest->request_sent_to === 'parent') {
            return $authUser instanceof User
                && ($authUser->role ?? null) === 'parent'
                && (int) $authUser->id === (int) $friendRequest->receiver_id;
        }

        if ($friendRequest->request_sent_to === 'kid') {
            if ($authUser instanceof User) {
                return Kid::where('id', $friendRequest->receiver_id)
                    ->where('parent_id', $authUser->id)
                    ->exists();
            }

            return false;
        }

        return false;
    }

    private function callerOwnsStory($authUser, Story $story): bool
    {
        if ($authUser instanceof Kid) {
            return (int) $story->kid_id === (int) $authUser->id;
        }

        return ($authUser->role ?? null) === 'parent' && (int) $story->user_id === (int) $authUser->id;
    }
}
