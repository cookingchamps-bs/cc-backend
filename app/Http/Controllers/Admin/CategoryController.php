<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Categories');
        $categories = Category::latest()->get();
        return view('admin.categories.index', compact('common', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Categories', null, 'Submit');
        return view('admin.categories.create', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'image'    => 'required|mimes:jpeg,jpg,png'
        ]);
        
        $category = Category::create([
             'name' => $request->category,
             'image' => Helper::handleFileUpload($request, 'image', 'categories'),
             'status' => 1
        ]);
        
        $notification = [
            'message' => 'Category added Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('categories.index', $notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail(decrypt($id));
        $common = Helper::getCommonData('Category detail');

        return view('admin.categories.show', compact('category', 'common'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $common = Helper::getCommonData('Edit Categories', null, 'update');
        $categories = Category::findOrFail(decrypt($id));
        
        return view('admin.categories.edit', compact('common','categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'category' => 'required|string',
            'image'    => 'nullable|mimes:jpeg,jpg,png'
        ]);
        
        $categories = Category::findOrFail(decrypt($id));
        
        $categories->update([
            'name'  =>  $request->category ?? $categories->name,
            'image' =>  $request->image ? Helper::handleFileUpload($request, 'image', 'categories') : $categories->image
        ]);
        
        $notification = [
            'message' => 'Category Updated Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('categories.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $categories = Category::findOrFail(decrypt($id));
            
            $cleanPath = str_replace('public/', '', $categories->image);
            $filePath = public_path($cleanPath);
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $categories->delete();
            
            $notification = [
                'message' => 'Categories deleted Successfully!',
                'alert-type' => 'success',
            ];
            
            return Helper::redirectFunction('categories.index', $notification);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Category delete failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to delete category.'], 500);
        }
    }
}
