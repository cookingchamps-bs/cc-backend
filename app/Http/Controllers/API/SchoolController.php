<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\School;
use App\Models\SchoolVideo;

class SchoolController extends Controller
{
    public function getSchool(){
        
        $schools = School::where('status', 1)
                  ->with(['videos' => function ($query) {
                    $query->where('is_active', 1)
                          ->orderBy('created_at', 'desc');
                }])
                ->latest()
                ->get();
                
        $schools->transform(function ($school) {
            if (! empty($school->school_logo)) {
                $variants = Helper::getImageVariants($school->school_logo);
                $school->school_logo_medium = $variants['medium'];
                $school->school_logo_thumb = $variants['thumb'];
            }
            
            $school->videos->transform(function ($video) {
                if (! empty($video->thumbnail_image)) {
                    $variants = Helper::getImageVariants($video->thumbnail_image);
                    $video->thumbnail_image_medium = $variants['medium'];
                    $video->thumbnail_image_thumb = $variants['thumb'];
                }

                return $video;
            });

            return $school;
        });
       
        return response([
            'status'=>'true',
            'code'=>200,
            'data'=>$schools
        ]);        
    }
    



    public function addView(Request $request)
    {
        $id = $request->id;
    
        if (!$id) {
            return response()->json(['message' => 'ID is required'], 400);
        }
    
        $video = SchoolVideo::find($id);
    
        if (!$video) {
            return response()->json(['message' => 'Video not found'], 404);
        }
    
        $video->views = ($video->views ?? 0) + 1;
        $video->save();
    
        return response()->json([
            'message' => 'View added',
            'views' => $video->fresh()->views
        ]);
    }
        
    
}
