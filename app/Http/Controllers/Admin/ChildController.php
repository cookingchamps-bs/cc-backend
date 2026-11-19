<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Kid;
use App\Models\UserSubscription;
use Illuminate\Http\Request;

class ChildController extends Controller
{
    // kid index
    public function Index(Request $request)
    {
        $common = [];
        $common['title'] = 'Kid List';
        $common['heading_title'] = 'Kid List';

        $kids = Kid::latest()->get();

        return view('admin.kids.index', compact('common', 'kids'));
    }

    // show page
    public function show(string $id)
    {
        $childDetail = Kid::query()
            ->with([
                'parent',
                'stories.storyAddedByParent',
                'stories.storyAddedByChild',
                'recipes.recipeAddedByParent',
                'recipes.recipeAddedByChild',
            ])
            ->findOrFail(decrypt($id));

        $subscriptions = UserSubscription::query()
            ->with(['subscriptionPlan', 'user'])
            ->where('kid_id', $childDetail->id)
            ->latest()
            ->get();

        return view('admin.kids.show', compact('childDetail', 'subscriptions'));
    }

    // edit page
    public function edit($id)
    {
        $common = $this->getCommonData('Edit Kid', 'Edit Kid', 'Update');
        $kids = Kid::findOrFail(decrypt($id));

        return view('admin.kids.edit', compact('common', 'kids'));
    }

    // update page
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'  => 'required',
            'day'   => 'required',
            'month' => 'nullable',
            'year'  => 'required',
            'grade' => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png',
        ]);


        $kids = Kid::findOrFail($id);
        // return $kids;
        
        $imagePath = $request->has('image') ? Helper::handleFileUpload($request, 'image', 'kid_image', $existingPath = null) : $kids->image;
        // return $imagePath;
        
        // if ($request->hasFile('image')) {
        //     $file = $request->file('image');
        //     $fileName = time().'.'.$file->getClientOriginalExtension();
        //     $path = $file->storeAs('image', $fileName, 'public');
        //     $Imagepath = 'public/storage/kid_image/'.$fileName;
        // }
        
        // if($request->has('image')){
        //     return "hello from image";
        // }
        
        // return "outside";
        
        $kids->update(
            [
                'name'  => $request->name,
                'day'   => $request->day,
                'month' => $request->month,
                'year'  => $request->year,
                'grade' => $request->grade,
                'kid_image' => $imagePath,
            ]
        );

        $notification = [
            'message' => 'Kids Detail Updated Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('kid.index', $notification);
    }

    public function destroy($id)
    {
        $kid = Kid::findOrFail(decrypt($id));

        Helper::deletePublicFile($kid->getRawOriginal('kid_image') ?? null);

        $kid->delete();

        $notification = [
            'message' => 'Kid deleted Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('kid.index', $notification);
    }

    /* ---- private common func -----*/
    private function getCommonData($title, $headingTitle = null, $button = null)
    {
        return [
            'title' => $title,
            'heading_title' => $headingTitle ?? $title,
            'button' => $button,
        ];
    }
}
