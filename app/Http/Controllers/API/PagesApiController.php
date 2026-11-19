<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CookingChampsIntro;
use App\Models\HelpAndSupport;
use Illuminate\Http\Request;
use App\Models\TermsAndCondition;
use App\Models\PrivacyPolicy;
use App\Helpers\Helper;

class PagesApiController extends Controller
{
    /**
     * help & support api
     */
    public function helpandSupport(Request $request)
    {
        $helpandSupport = HelpAndSupport::all();

        if ($helpandSupport->isEmpty()) {
            return Helper::responseFunction(true, 200, 'Data not found!', null, 404);
        }

        return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $helpandSupport, 200);
    }

    /**
     * about us api
     */
    public function aboutUs(Request $request)
    {
        try {
            $cookingChampsIntro = $request->id ? CookingChampsIntro::find($request->id) : CookingChampsIntro::all();

            if (! $cookingChampsIntro) {
                return Helper::responseFunction(true, 200, 'Data not found!', null, 404);
            }

            return Helper::responseFunction(true, 200, 'Data retrieve successfully!', $cookingChampsIntro, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'status_code' => 422, 'message' => $e->getMessage()], 422);
        }
    }
    
    /**
     * t&c api
     */ 
    public function termsAndCondition(Request $request)
    {
        $termsAndCondition = TermsAndCondition::first('description');
        if(!$termsAndCondition){
            return response()->json(['status' => false,'message' => 'Terms & Condition not found!','data' => []]);
        }
        
        return response()->json(['status' => true,'message'=>'Data Retrieved Successfully!','data' => $termsAndCondition]);
    }
    
    /**
     * privacy policy api
     */ 
    public function privacyPolicy(Request $request)
    {
        $privacypolicy = PrivacyPolicy::first('description');
        if(!$privacypolicy){
            return response()->json(['status' => false,'message'=>'Privacy policy not found','data' => []]);
        }
        
        return response()->json(['status' => true,'message' => 'Data retrieved successfully!','data' => $privacypolicy]);
    }
}
