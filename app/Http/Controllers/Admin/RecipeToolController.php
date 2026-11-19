<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\RecipeTool;
use Illuminate\Http\Request;

class RecipeToolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = $this->getCommonData('Recipe Tools');
        $recipe_tool = RecipeTool::latest()->get();

        return view('admin.recipe_tools.index', compact('common', 'recipe_tool'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = $this->getCommonData('Add Recipe Tool', 'Add Recipe Tool', 'Submit');

        return view('admin.recipe_tools.create', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|mimes:jpeg,jpg,png',
            'name' => 'required',
        ]);

        RecipeTool::create([
            'tool_image' => $this->handleFileUpload($request, 'image', 'recipe_tool', $existingPath = null),
            'tool_name' => $request->name,
        ]);

        $notification = [
            'message' => 'Recipe Tool added Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('recipe-tool.index', $notification);
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
        $common = Helper::getCommonData('Edit Recipe Tool', 'Edit Recipe Tool', 'Update');
        $recipeTools = RecipeTool::findOrFail(decrypt($id));
       
        return view('admin.recipe_tools.edit', compact('common', 'recipeTools'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png',

        ]);

    
        $recipeTools = RecipeTool::findOrFail($id);
        $recipeTools->update(
            [
                'tool_name' => $request->name,
                'tool_image' => $request->has('image') ? $this->handleFileUpload($request, 'image', 'recipe_tool', $existingPath = null) : $recipeTools->tool_image,
            ]
        );

        $notification = [
            'message' => 'Recipe Tool Updated Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('recipe-tool.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $recipeTools = RecipeTool::findOrFail(decrypt($id));

        Helper::deletePublicFile($recipeTools->getRawOriginal('tool_image') ?? $recipeTools->tool_image);

        $recipeTools->delete();

        $notification = [
            'message' => 'Recipe Tool deleted Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('recipe-tool.index', $notification);

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

    // file upload function
    private function handleFileUpload(Request $request, $fileKey, $storagePath, $existingPath = null)
    {
        if ($request->hasFile($fileKey)) {
            $file = $request->file($fileKey);
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->storeAs($storagePath, $fileName, 'public');

            return "storage/$storagePath/$fileName";
        }

        return $existingPath;
    }
}
