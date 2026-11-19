<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\{User,Kid,Subscription};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ChildController extends Controller
{
    /**
     * child listing api
     */
    public function myChildListing(Request $request)
    {
        try {
            $user = Auth::user();
            
            // check if parent purchase subscription
            // $IsParentSubscriptionExists = $user->userSubscription->isNotEmpty();
            $IsParentSubscriptionExists = $user->userSubscription()->exists();
            
            $getSubscriptionPrice = Helper::getSubscriptionPrice();
            
            $hasSubscribedKid = Kid::where('parent_id', $user->id)
                                    ->whereHas('userSubscription')
                                    ->exists();
                                    
            $plan = $hasSubscribedKid || $IsParentSubscriptionExists
                ? $getSubscriptionPrice['additional_kid_amount']
                : $getSubscriptionPrice['first_kid_amount'];

            $subscription_amount = $plan ? [
                'id' => (string) $plan->id,
                'amount' => (string) $plan->amount,
                'stripe_price_id' => (string) ($plan->stripe_price_id ?? ''),
            ] : null;
          
            $allKids = Kid::with('subscription')->where('parent_id', $user->id)->latest()->paginate(10);
            if ($allKids->isEmpty()) {
                return Helper::responseFunction(true, 200, 'No kid found!', [
                    'total_page' => $allKids->lastPage(),
                    'subscription_amount'=> $subscription_amount,
                    'items' => $allKids->items(),
                    'is_parent_purchase_subscription' => $IsParentSubscriptionExists
                ], 200);
            }

            return Helper::responseFunction(true, 200, 'Kids retrieve successfully!', [
                'total_page' => $allKids->lastPage(),
                'subscription_amount'=> $subscription_amount,
                'items' => $allKids->items(),
                'is_parent_purchase_subscription' => $IsParentSubscriptionExists
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'code' => 422, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * delete child api
     */
    public function deleteKid(Request $request)
    {
        try {
            // retrieve user
            $user = auth()->user();

            $kid = Kid::where(['id' => $request->kid_id, 'parent_id' => $user->id])->first();

            if (empty($kid)) {
                return Helper::responseFunction(false, 404, 'Kid not found!', null, 404);
            }

            $kid->delete();

            return Helper::responseFunction(true, 200, 'Kid deleted successfully!', null, 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'code' => 422, 'message' => $e->getMessage()], 422);
        }
    }
}
