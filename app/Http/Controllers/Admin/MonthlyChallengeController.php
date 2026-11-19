<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MonthlyChallenge;
use App\Helpers\Helper;

class MonthlyChallengeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Monthly Challenge');
        $monthlyChallenge = MonthlyChallenge::latest()->get();

        return view('admin.monthly_challenge.index', compact('common', 'monthlyChallenge'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Monthly Challenge', 'Add Monthly Challenge', 'Submit');
        return view('admin.monthly_challenge.create', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required',
            'description' => 'required',
            'image'       => 'required|mimes:jpeg,jpg,png',
        ]);
        
        $monthlyChallenge = MonthlyChallenge::create([
            'title' => $request->title,
            'banner_image' => Helper::handleFileUpload($request, 'image', 'monthly_challenge', $existingPath = null),
            'description' => $request->description
        ]);
        
        $notification = [
            'message' => 'Monthly Challenge added Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('monthly-challenge.index', $notification);
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
        $common = Helper::getCommonData('Edit Monthly Challenge', 'Edit Monthly Challenge', 'Update');
        $monthlyChallenge = MonthlyChallenge::find(decrypt($id));
        
        return view('admin.monthly_challenge.edit', compact('common', 'monthlyChallenge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'title'       => 'required',
            'description' => 'required',
            'image'       => 'nullable|mimes:jpeg,jpg,png',
        ]);
        
        
        $monthlyChallenge = MonthlyChallenge::find($id);
        
        $monthlyChallenge->update([
            'title' => $request->title ?? $monthlyChallenge->title,
            'banner_image' => $request->hasFile('image') ? Helper::handleFileUpload($request, 'image', 'monthly_challenge', $existingPath = null) : $monthlyChallenge->banner_image,
            'description' => $request->description ?? $monthlyChallenge->description
        ]);
        
        $notification = [
            'message' => 'Monthly Challenge Updated Successfully!',
            'alert-type' => 'success',
        ];
        
        return redirect()->back()->with($notification);

        // return Helper::redirectFunction('monthly-challenge.index', $notification);
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $monthlyChallenge = MonthlyChallenge::findOrFail(decrypt($id));

            Helper::deletePublicFile($monthlyChallenge->getRawOriginal('banner_image') ?? $monthlyChallenge->banner_image);

            $monthlyChallenge->delete();

            $notification = [
                'message' => 'Monthly Challenge deleted Successfully!',
                'alert-type' => 'success',
            ];

            return Helper::redirectFunction('monthly-challenge.index', $notification);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Monthly challenge delete failed: '.$e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to delete monthly challenge.'], 500);
        }
    }
    
    /* --------update challenge status ------ */
    public function updateMonthlyChallengeStatus(Request $request)
    {
        try {
            $upateMonthlyChallengeStatus = MonthlyChallenge::findOrFail($request->monthly_challenge_id);
            $upateMonthlyChallengeStatus->status = $request->status == 'Active' ? 0 : 1;
            $upateMonthlyChallengeStatus->save();

            return response()->json(['success' => true, 'status' => $upateMonthlyChallengeStatus->status, 'message' => 'Status updated successfully.']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }
    
    /*  ---- get current monthly challenge -----*/
    public function getCurrentMonthlyChallenge(Request $request)
    {
        $currentMonthlyChallenge = MonthlyChallenge::where('status', 1)->first();
        return response()->json($currentMonthlyChallenge);
    }
}
