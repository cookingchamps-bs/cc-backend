<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GrowingActivity;
use App\Helpers\Helper;
use App\Helpers\HtmlSanitizer;
use Illuminate\Support\Facades\Log;

class GrowingActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Growing Activity');
        $growingActivities = GrowingActivity::latest()->get();

        return view('admin.growing_activity.index', compact('common', 'growingActivities'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Growing activity', 'Add Growing Activity', 'Submit');
        return view('admin.growing_activity.create', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizeDescription($request);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'required|file|mimes:jpeg,jpg,png,webp,gif|max:10240',
        ]);

        try {
            $imagePath = Helper::handleFileUpload($request, 'image', 'growing_activity', null);
            if (empty($imagePath)) {
                return back()
                    ->withInput()
                    ->with(['message' => 'Image upload failed. Please try another JPEG or PNG.', 'alert-type' => 'error']);
            }

            // App home uses the first active growing activity — activate the new one.
            GrowingActivity::query()->update(['status' => 0]);

            GrowingActivity::create([
                'title' => $request->title,
                'image' => $imagePath,
                'description' => HtmlSanitizer::richText($request->description),
                'status' => 1,
            ]);
        } catch (\Throwable $e) {
            Log::error('Growing activity store failed: '.$e->getMessage(), ['exception' => $e]);

            return back()
                ->withInput()
                ->with(['message' => 'Could not save growing activity.', 'alert-type' => 'error']);
        }

        $notification = [
            'message' => 'Growing Activity added Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('growing-activity.index', $notification);
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
        $common = Helper::getCommonData('Edit Growing Activity', 'Edit Growing Activity', 'Update');
        $growingActivities = GrowingActivity::find(decrypt($id));

        return view('admin.growing_activity.edit', compact('common', 'growingActivities'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $this->normalizeDescription($request);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'image'       => 'nullable|file|mimes:jpeg,jpg,png,webp,gif|max:10240',
        ]);

        $growingActivities = GrowingActivity::findOrFail($id);

        try {
            $imagePath = $growingActivities->getRawOriginal('image');
            if ($request->hasFile('image')) {
                $uploaded = Helper::handleFileUpload($request, 'image', 'growing_activity', $imagePath);
                if (empty($uploaded)) {
                    return back()
                        ->withInput()
                        ->with(['message' => 'Image upload failed. Please try another JPEG or PNG.', 'alert-type' => 'error']);
                }
                $imagePath = $uploaded;
            }

            $growingActivities->update([
                'title' => $request->title,
                'image' => $imagePath,
                'description' => HtmlSanitizer::richText($request->description),
            ]);
        } catch (\Throwable $e) {
            Log::error('Growing activity update failed: '.$e->getMessage(), ['exception' => $e]);

            return back()
                ->withInput()
                ->with(['message' => 'Could not update growing activity.', 'alert-type' => 'error']);
        }

        $notification = [
            'message' => 'Growing Activity Updated Successfully!',
            'alert-type' => 'success',
        ];

        return Helper::redirectFunction('growing-activity.index', $notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $growingActivities = GrowingActivity::findOrFail(decrypt($id));

            $relative = Helper::normalizeImagePath($growingActivities->getRawOriginal('image'));
            if ($relative !== '') {
                $filePath = public_path($relative);
                if (is_file($filePath)) {
                    @unlink($filePath);
                }
            }

            $growingActivities->delete();

            $notification = [
                'message' => 'Growing Activity deleted Successfully!',
                'alert-type' => 'success',
            ];

            return Helper::redirectFunction('growing-activity.index', $notification);
        } catch (\Exception $e) {
            return back()->with(['message' => 'Unable to update status.', 'alert-type' => 'error']);
        }
    }

    // UPDATE GROWING ACTIVITY STATUS
    public function updateGrowingActivityStatus(Request $request)
    {
        try {
            $upateGrowingActivityStatus = GrowingActivity::findOrFail($request->growing_activity_id);

            $newStatus = $request->status == 'Active' ? 0 : 1;
            if ($newStatus === 1) {
                GrowingActivity::query()->where('id', '!=', $upateGrowingActivityStatus->id)->update(['status' => 0]);
            }

            $upateGrowingActivityStatus->status = $newStatus;
            $upateGrowingActivityStatus->save();

            return response()->json([
                'success' => true,
                'status' => $upateGrowingActivityStatus->status,
                'message' => 'Status updated successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status.',
            ], 500);
        }
    }

    /**
     * Decode WAF-safe base64 payload from the form, then treat empty Summernote markup as blank.
     */
    private function normalizeDescription(Request $request): void
    {
        if ($request->filled('description_b64')) {
            $decoded = base64_decode((string) $request->input('description_b64'), true);
            if ($decoded !== false) {
                $request->merge(['description' => $decoded]);
            }
        }

        $description = (string) $request->input('description', '');
        $plain = trim(html_entity_decode(strip_tags($description)));
        if ($plain === '') {
            $request->merge(['description' => '']);
        }
    }
}
