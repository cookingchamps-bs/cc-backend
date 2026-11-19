<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\ProductCategory;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Product Category');
        $productCategories = ProductCategory::latest()->get();
        return view('admin.product_categories.index', compact('common', 'productCategories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Product Categories', null, 'Submit');
        return view('admin.product_categories.create', compact('common'));
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
        
        $productCategory = ProductCategory::create([
             'name' => $request->category,
             'image' => Helper::handleFileUpload($request, 'image', 'product_categories'),
             'status' => 1
        ]);
        
        $notification = [
            'message' => 'Product Category added Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('product-categories.index', $notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $common = Helper::getCommonData('Edit Product Categories', null, 'update');
        $productcategories = ProductCategory::findOrFail(decrypt($id));
        
        return view('admin.product_categories.edit', compact('common','productcategories'));
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
        
        $productCategory = ProductCategory::findOrFail(decrypt($id));
        
        $productCategory->update([
            'name'  =>  $request->category ?? $productCategory->name,
            'image' =>  $request->image ? Helper::handleFileUpload($request, 'image', 'product_categories') : $productCategory->image
        ]);
        
        $notification = [
            'message' => 'Product Category updated Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('product-categories.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $productcategory = ProductCategory::findOrFail(decrypt($id));
            
            
            $cleanPath = str_replace('public/', '', $productcategory->image);
            $filePath = public_path($cleanPath);
            
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $productcategory->delete();
            
            $notification = [
                'message' => 'Product Categories deleted Successfully!',
                'alert-type' => 'success',
            ];
            
            return Helper::redirectFunction('product-categories.index', $notification);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Product category delete failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to delete product category.'], 500);
        }
    }
}
