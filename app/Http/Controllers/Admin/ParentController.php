<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    // parent index
    public function Index(Request $request)
    {
        $common = Helper::getCommonData('Parent List');
        $users = User::latest()->get();

        return view('admin.parent.index', compact('common', 'users'));
    }

    // edit page
    public function edit($id)
    {
        $common = Helper::getCommonData('Edit Parent', 'Edit Parent', 'Update');
        $users = User::findOrFail(decrypt($id));

        return view('admin.parent.edit', compact('common', 'users'));
    }

    // show page
    public function show(string $id)
    {
        $parentDetail = User::query()
            ->with([
                'kids',
                'recipes.recipeAddedByParent',
                'recipes.recipeAddedByChild',
                'stories.storyAddedByParent',
                'stories.storyAddedByChild',
                'orders' => fn ($q) => $q->withCount('items')->latest()->limit(15),
                'subscriptionPurchases' => fn ($q) => $q
                    ->with(['subscriptionPlan', 'kid'])
                    ->latest(),
            ])
            ->findOrFail(decrypt($id));

        return view('admin.parent.show', compact('parentDetail'));
    }

    // update page
    public function update(Request $request, $id)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'nullable|email',
            'image' => 'nullable|mimes:jpeg,jpg,png',

        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('image', $fileName, 'public');
            $Imagepath = 'storage/image/'.$fileName;
        }

        $users = User::findOrFail($id);

        $users->update(
            [
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'image' => $request->has('image') ? $Imagepath : $users->image,
            ]
        );

        $notification = [
            'message' => 'Parent Detail Updated Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('parent.index', $notification);
    }

    // delete parent
    public function destroy($id)
    {
        $users = User::findOrFail(decrypt($id));

        Helper::deletePublicFile($users->getRawOriginal('image') ?? $users->image);

        $users->delete();

        $notification = [
            'message' => 'Parent deleted Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('parent.index', $notification);

    }
}
