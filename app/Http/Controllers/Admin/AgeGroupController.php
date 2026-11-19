<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\AgeGroup;

class AgeGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Age Group');
        $ageGroups = AgeGroup::latest()->get();
        return view('admin.age_groups.index', compact('common', 'ageGroups'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Age Group', null, 'Submit');
        return view('admin.age_groups.create', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'age_from' => 'required|integer',
            'age_to'    => 'required|integer'
        ], [
            'age_from' => 'Range field is required',
            'age_from.integer' => 'Range field must be integer',
            'age_to' => 'Range field is required',
            'age_to.integer' => 'Range field must be integer'
        ]);
        
        $ageGroup = AgeGroup::create([
             'age_from' => $request->age_from,
             'age_to'   => $request->age_to,
        ]);
        
        $notification = [
            'message' => 'Age Group added Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('age-group.index', $notification);
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
        $common = Helper::getCommonData('Edit Age Group', null, 'update');
        $ageGroup = AgeGroup::findOrFail(decrypt($id));
        
        return view('admin.age_groups.edit', compact('common','ageGroup'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'age_from' => 'required|integer',
            'age_to'    => 'required|integer'
        ], [
            'age_from' => 'Range field is required',
            'age_from.integer' => 'Range field must be integer',
            'age_to' => 'Range field is required',
            'age_to.integer' => 'Range field must be integer'
        ]);
        
        $ageGroup = AgeGroup::findOrFail(decrypt($id));
        
        $ageGroup->update([
            'age_from'  =>  $request->age_from ?? $ageGroup->age_from,
            'age_to' =>  $request->age_to ?? $ageGroup->age_to
        ]);
        
        $notification = [
            'message' => 'Age Group Updated Successfully!',
            'alert-type' => 'success',
        ];
        
        return Helper::redirectFunction('age-group.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $ageGroup = AgeGroup::findOrFail(decrypt($id));
            $ageGroup->delete();
            
            $notification = [
                'message' => 'Age Group deleted Successfully!',
                'alert-type' => 'success',
            ];
            
            return Helper::redirectFunction('age-group.index', $notification);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Age group delete failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to delete age group.'], 500);
        }
    }
}
