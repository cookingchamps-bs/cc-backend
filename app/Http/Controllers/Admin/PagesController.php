<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CookingChampsIntro;
use App\Models\HelpAndSupport;
use App\Models\TermsAndCondition;
use App\Models\PrivacyPolicy;
use App\Helpers\HtmlSanitizer;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    // help and support
    public function helpAndSupport(Request $request)
    {
        $common = [];
        $common['title'] = 'Help & Support';
        $common['button'] = 'Update';
        $helpandSupport = HelpAndSupport::where('id', 1)->first();

        if ($request->isMethod('post')) {
            $request->validate([
                'title' => 'required',
            ]);

            $helpandSupport = HelpAndSupport::where('id', 1)->update([
                'title' => $request->title,
                'description' => HtmlSanitizer::richText($request->description),
            ]);

            return redirect()->back()->with('success_message', 'Help & Support Updated Successfully');
        }

        return view('admin.help_&_support.index', compact('common', 'helpandSupport'));
    }

    // cooking champs intro
    public function aboutUs(Request $request)
    {
        $sections = [
            'introduction-to-cookingchamps' => [
                'id' => 1,
                'title' => 'Cooking Champs Introduction',
                'success_message' => 'Introduction to CookingChamps updated successfully',
            ],
            'message-for-parents' => [
                'id' => 2,
                'title' => 'Message for Parents',
                'success_message' => 'Message for Parents updated successfully',
            ],
            'message-to-cookingchamps' => [
                'id' => 3,
                'title' => 'Message to Cooking Champs',
                'success_message' => 'Message to CookingChamps updated successfully',
            ],
            'cookingchamps-moto' => [
                'id' => 4,
                'title' => 'Cooking Champs Moto',
                'success_message' => 'Cooking Champs Moto updated successfully',
            ],
        ];

        // Get the section based on request type
        $type = $request->type;
        if (! isset($sections[$type])) {
            abort(404, 'Invalid section type');
        }

        $section = $sections[$type];

        $common = [
            'title' => $section['title'],
            'button' => 'Update',
        ];

        $cookingChamps = CookingChampsIntro::find($section['id']);

        if ($request->isMethod('post')) {
            // Validate the request
            $request->validate([
                'title' => 'required',
                'image' => 'nullable|mimes:jpeg,jpg,png',
                'description' => 'required',
                'color_code' => 'nullable',
            ]);

            // Handle image upload
            $imagePath = $cookingChamps->image;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time().'.'.$file->getClientOriginalExtension();
                $imagePath = $file->storeAs('aboutus', $fileName, 'public');
                $imagePath = 'storage/aboutus/'.$fileName;
            }

            // Update the record
            $cookingChamps->update([
                'title' => $request->title,
                'image' => $imagePath,
                'description' => HtmlSanitizer::richText($request->description),
                'color_code' => $request->color_code,
            ]);

            return redirect()->back()->with('success_message', $section['success_message']);
        }

        return view('admin.aboutus.index', compact('common', 'cookingChamps'));
    }
    
    // Terms And Condition
    
    public function termsAndCondition(Request $request){
        
        $common = [];
        $common['title'] = 'Terms And Condition';
        $common['button'] = 'update';
        $termsAndCondition = TermsAndCondition::where('id', 1)->first();
        
        if($request->isMethod('post')){
            $request->validate([
                'title' => 'required|string',
                'description' =>'required|string',
             ]);
        
            $termsAndCondition = TermsAndCondition::where('id', 1)->update([
                'title' => $request->title,
                'description' => HtmlSanitizer::richText($request->description),
            ]);
            return redirect()->back()->with('success_message','Terms And Conditions Updated Successfully!');
        }
        
        return view('admin.terms_&_condition.create', compact('common','termsAndCondition'));
    }
    
      public function privacyPolicy(Request $request){
        
        $common = [];
        $common['title'] = 'Privacy Policy';
        $common['button'] = 'update';
        $PrivacyPolicy = PrivacyPolicy::where('id', 1)->first();
        
        if($request->isMethod('post')){
            $request->validate([
                'title' => 'required|string',
                'description' =>'required|string',
             ]);
        
            $PrivacyPolicy = PrivacyPolicy::where('id', 1)->update([
                'title' => $request->title,
                'description' => HtmlSanitizer::richText($request->description),
            ]);
            return redirect()->back()->with('success_message','Privacy Policy Updated Successfully!');
        }
        
        return view('admin.privacy_policy.create', compact('common','PrivacyPolicy'));
    }
}
