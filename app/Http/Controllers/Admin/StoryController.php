<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Helpers\HtmlSanitizer;
use App\Http\Controllers\Controller;
use App\Models\Story;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Story List');
        $stories = Story::latest()->get();

        return view('admin.stories.index', compact('common', 'stories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Story', 'Add Story', 'Submit');
    
        return view('admin.stories.create', compact('common'));
    }

    /*---------------------------- Store stories.-----------------------------------*/
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image'   => 'required|mimes:jpeg,jpg,png'
        ]);
        
        
        //------------ insert recipe -----------------------------------------------
        $story = Story::create([
            'role' => 'admin',
            'title' => $request->title,
            'video_link' => isset($request->video_link) ? $request->video_link:'',
            'image' => Helper::handleFileUpload($request, 'image', 'story_images', $existingPath = null),
            'description' => HtmlSanitizer::richText($request->description),
        ]);
        
        $notification = [
            'message' => 'Story added Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('story.index', $notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $story = Story::with([
            'storyImages',
            'storyAddedByParent',
            'storyAddedByChild',
        ])->findOrFail(decrypt($id));

        return view('admin.stories.show', compact('story'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $common = Helper::getCommonData('Edit Story', 'Edit Story', 'Update');
        $story = Story::find(decrypt($id));

        return view('admin.stories.edit', compact('common', 'story'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title'       => 'required',
            'description' => 'required',
            'image'       => 'nullable|mimes:jpeg,jpg,png'
        ]);
        
        $story = Story::with('storyImages')->find($id);
        $story->update([
            'title' => $request->title ?? $story->title,
            'video_link' => isset($request->video_link) ? $request->video_link: $story->video_link,
            'image' => $request->has('image') ? Helper::handleFileUpload($request, 'image', 'story_images', $existingPath = null) : $story->image,
            'description' => HtmlSanitizer::richText($request->description ?? $story->description),
        ]);
        
        $notification = [
            'message'    => 'Story Updated Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('story.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $stories = Story::findOrFail(decrypt($id));

            Helper::deletePublicFile($stories->getRawOriginal('image') ?? $stories->image);

            $stories->delete();

            $notification = [
                'message' => 'Story deleted Successfully!',
                'alert-type' => 'success',
            ];

            return Helper::redirectFunction('story.index', $notification);

        } catch (\Exception $e) {
            Log::error('Story delete failed: '.$e->getMessage());

            return response()->json(['status' => false, 'message' => 'Unable to delete story.'], 500);
        }
    }

    /* --- update Best from the champ status --- */
    public function updateBestFromtheChampStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:stories,id',
            'status' => 'required|in:0,1',
        ]);

        try {
            $updateBestfromtheChampStatus = Story::findOrFail($request->id);
            $updateBestfromtheChampStatus->status = $request->status;
            $updateBestfromtheChampStatus->save();

            return response()->json(['success'=>true, 'message'=>'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }

    /* --- update featured stories status --- */
    public function updateFeaturedStoryStatus(Request $request)
    {
        try {
            $featuredStoryStatus = Story::findOrFail($request->story_id);
            $featuredStoryStatus->is_featured = $request->status == 'Active' ? 0 : 1;
            $featuredStoryStatus->save();

            return response()->json([
                'success' => true,
                'status' => $featuredStoryStatus->is_featured,
                'message' => 'Status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }
    
    /* ---- Featured Story Listing ------ */
    public function featuredStory(Request $request)
    {
        $common = Helper::getCommonData('Featured Stories');
        $stories = Story::where('is_featured', 1)->latest()->get();

        return view('admin.stories.featured_stories', compact('common', 'stories'));
    }
    
    /* ---- Best from the champs ------- */
    public function BestFromTheChamp(Request $request)
    {
        $common = Helper::getCommonData('Best from the Champ');
        $stories = Story::where('status', 1)->latest()->get();
    
        return view('admin.stories.best_from_champ', compact('common', 'stories'));
    }
}
