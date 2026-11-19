<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Log};
use App\Models\{Address, Kid, Subscription, User, UserSubscription};
use App\Services\StripeCheckoutCustomer;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Carbon\Carbon;


class PaymentController extends Controller
{
    // purchase subscription
    public function purchasePlan(Request $request)
    {
        $userId = $request->query('user_id');
        $kidId = $request->query('kid_id');
        $subscriptionId = $request->query('subscription_id');
        
        if(!$userId || !$subscriptionId){
            return response()->json(['status'=>false,'status_code' =>422, 'message'=>'User Id & subscription id are required!'],422);
        }
        
        if ($request->filled('kid_id')) {
            $kid = Kid::where('id', $kidId)->where('parent_id', $userId)->first();
            if (! $kid) {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'Kid not found or does not belong to this user.',
                ], 403);
            }
        }

    
        // if(!$userId || !$kidId){
        //     return response()->json(['status'=>false,'status_code' =>422, 'message'=>'User Id, Subscription Id & kid id are required!'],422);
        // }
        
        // if(!$subscriptionId) return response()->json(['status'=>false,'status_code' =>422, 'message'=>'Subscription Id is required!'],422);
        
        $users = User::withCount('kids')->with('kids')->find($userId);
        if(!$users) return response()->json(['status'=>false,'status_code' =>200, 'message'=>'User not found or invalid user Id!'],404);
        
        
        $subscription = Subscription::find($subscriptionId)
            ?? Subscription::where('stripe_price_id', $subscriptionId)->first();
        if(!$subscription) return response()->json(['status'=>false,'status_code' =>404, 'message'=>'Subscription not found!'],404);
        
        
        Stripe::setApiKey(Helper::getStripeSecret());
        
        
        // ----------- stripe checkout --------- //
        try{
            $savedAddress = Address::where('parent_id', $userId)->latest('id')->first();

            $checkoutSession = Session::create(array_merge([
                    'payment_method_types' => ['card'],
                    'mode' => 'payment',
                    'line_items' => [[
                        'price' => $subscription->stripe_price_id,
                        'quantity' => 1,
                    ]],
                    'success_url' => route('payment.success').'?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url'  => route('payment.cancel'),
                ], StripeCheckoutCustomer::sessionParams($users, $savedAddress)));
                
            session([
                'stripe_session_id' => $checkoutSession->id,
                'stripe_price_id' => $subscription->stripe_price_id,
                'user_id' => $userId,
                'kid_id'  => $kidId ?? null
            ]);
        
            return redirect($checkoutSession->url);
        }catch(\Exception $e){
            Log::error('Subscription DB error', ['error' => $e->getMessage()]);
            return back()->withErrors(['error' => 'Stripe Error: ' . $e->getMessage()]);
        }
    }
    
    // New subscription success url
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id') ?? session('stripe_session_id');
        $priceId = session('stripe_price_id');
        $userId = session('user_id');
        $kidId = session('kid_id');

        session()->forget(['stripe_session_id', 'stripe_price_id', 'user_id', 'kid_id']);

        if (empty($sessionId)) {
            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => 'Missing Stripe session.',
            ], 400);
        }

        Stripe::setApiKey(Helper::getStripeSecret());

        try {
            $stripeSession = Session::retrieve($sessionId);
            if (($stripeSession->payment_status ?? null) !== 'paid') {
                return response()->json([
                    'status' => false,
                    'status_code' => 402,
                    'message' => 'Payment not completed.',
                ], 402);
            }

            $priceId = $priceId ?: ($stripeSession->metadata->stripe_price_id ?? null);
            $userId = $userId ?: (int) ($stripeSession->metadata->user_id ?? 0);
            $kidId = $kidId ?: ($stripeSession->metadata->kid_id ?? null);
        } catch (\Exception $e) {
            Log::error('Legacy payment success could not verify Stripe session', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => false,
                'status_code' => 400,
                'message' => 'Unable to verify payment.',
            ], 400);
        }

        if (UserSubscription::where('transaction_id', $sessionId)->exists()) {
            return response()->json([
                'status' => true,
                'status_code' => 200,
                'message' => 'Subscription already applied.',
            ], 200);
        }

        $subscription = Subscription::where('stripe_price_id', $priceId)->first();

        $user = User::find($userId);

        if (! $subscription || ! $user) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Invalid user or subscription',
            ], 404);
        }

        try {
            UserSubscription::create([
                'user_id'            => $userId,
                'kid_id'             => $kidId,
                'subscription_id'    => $subscription->id,
                'role'               => 'parent',
                'sub_role'           => $user->role === 'individual' ? 'individual' : null,
                'transaction_id'     => $sessionId,
                'stripe_product_id'  => $subscription->stripe_product_id,
                'stripe_price_id'    => $priceId,
                'payment_status'     => 1,
                'start_date'         => now(),
                'end_date'           => null,
                'is_active'          => 1,
            ]);
        
            return response()->json([
                'status' => true,
                'status_code' => 201,
                'message' => 'Subscription purchased successfully!',
            ], 201);
        }catch (\Exception $e){
            Log::error('Subscription DB error', ['error' => $e->getMessage()]);

            return response()->json([
                'status'      => false,
                'status_code' => 500,
                'message'     => 'Something went wrong while applying the subscription.',
            ], 500);
        }
    
    }
    
    // cancel url
    public function cancel()
    {
        session()->forget(['stripe_session_id', 'stripe_price_id', 'user_id', 'kid_id']);

        Log::info('Stripe payment cancelled by user.');
        
        return response()->json([
            'status' => false,
            'status_code' => 422,
            'message' => 'Payment failed.',
        ], 422);
    }
    
    // Old Subscription working purchase plan
    // public function purchasePlan(Request $request)
    // {
    //     $userId = $request->query('user_id');
    //     $subscriptionId = $request->query('subscription_id');
        
    //     if(!$userId || !$subscriptionId){
    //         return response()->json(['status'=>false,'status_code' =>422, 'message'=>'User Id & subscription id are required!'],422);
    //     }
        
    //     $users = User::withCount('kids')->with('kids')->find($userId);
    //     if(!$users){
    //         return response()->json(['status'=>false,'status_code' =>200, 'message'=>'User not found!'],404);
    //     }
        
    //     $subscription = Subscription::find($subscriptionId);
    //     if(!$subscription){
    //         return response()->json(['status'=>false,'status_code' =>200, 'message'=>'Subscription not found!'],404);
    //     }
        
    //     $kidIds = $users->kids->pluck('id');
    //     $totalQuantity = 1 + $users->kids_count;

    //     Stripe::setApiKey(config('services.stripe.STRIPE_SECRET'));
        
    //     // ----------- stripe checkout --------- //
    //     try{
    //         $checkoutSession = Session::create([
    //                 'payment_method_types' => ['card'],
    //                 'mode' => 'subscription',
    //                 'line_items' => [[
    //                     'price' => $subscription->stripe_price_id,
    //                     'quantity' => $totalQuantity,
    //                 ]],
    //                 'success_url' => route('payment.success'),
    //                 'cancel_url'  => route('payment.cancel'),
    //             ]);
                
    //         session([
    //             'stripe_session_id' => $checkoutSession->id,
    //             'stripe_price_id' => $subscription->stripe_price_id,
    //             'user_id' => $userId,
    //             'kid_id'  => $kidIds
    //         ]);
        
    //         return redirect($checkoutSession->url);
    //     }catch(\Exception $e){
    //         return back()->withErrors(['error' => 'Stripe Error: ' . $e->getMessage()]);
    //     }
    // }
    
    // Old Subscription working success url
    // public function success()
    // {
    //     // Log::info('Stripe Session Data:', session()->all());
    
    //     // Fetch session data
    //     $sessionId = session('stripe_session_id');
    //     $priceId   = session('stripe_price_id');
    //     $userId    = session('user_id');
    //     $kidIds    = session('kid_id');
    
    //     session()->forget(['stripe_session_id', 'stripe_price_id', 'user_id', 'kid_id']);
    
    //     // Load subscription and user
    //     Stripe::setApiKey(config('services.stripe.STRIPE_SECRET'));
    //     $subscription = Subscription::where('stripe_price_id', $priceId)->first();
    //     $user = User::findOrFail($userId);
    
    //     if (!$subscription || !$user) {
    //         return response()->json([
    //             'status' => false,
    //             'status_code' => 404,
    //             'message' => 'Invalid user or subscription',
    //         ], 404);
    //     }
    
    //     // Set subscription dates
    //     $startDate = Carbon::today();
    //     $endDate   = ($subscription->type === 'month') ? $startDate->copy()->addMonth() 
    //                     : $startDate->copy()->addYear();
                        
    //     DB::beginTransaction();
        
    //     try{
    //         // First: insert for parent
    //         UserSubscription::create([
    //             'user_id'            => $userId,
    //             'kid_id'             => null,
    //             'subscription_id'    => $subscription->id,
    //             'role'               => 'parent',
    //             'sub_role'           => $user->role === 'individual' ? 'individual' : null,
    //             'transaction_id'     => $sessionId,
    //             'stripe_product_id'  => $subscription->stripe_product_id,
    //             'stripe_price_id'    => $priceId,
    //             'payment_status'     => 1,
    //             'start_date'         => $startDate,
    //             'end_date'           => $endDate,
    //             'is_active'          => 1,
    //         ]);
        
    //         if(!empty($kidIds)){
    //             foreach ($kidIds as $kidId) {
    //                 UserSubscription::create([
    //                     'user_id'            => $userId,  // always parent ID
    //                     'kid_id'             => $kidId,
    //                     'subscription_id'    => $subscription->id,
    //                     'role'               => 'kid',
    //                     'sub_role'           => null,
    //                     'transaction_id'     => $sessionId,
    //                     'stripe_product_id'  => $subscription->stripe_product_id,
    //                     'stripe_price_id'    => $priceId,
    //                     'payment_status'     => 1,
    //                     'start_date'         => $startDate,
    //                     'end_date'           => $endDate,
    //                     'is_active'          => 1,
    //                 ]);
    //             }
    //         }
            
    //         DB::commit();
    
    //         return response()->json([
    //             'status' => true,
    //             'status_code' => 201,
    //             'message' => 'Subscription successfully applied to user and their kids.',
    //         ], 201);
    //     }catch (\Exception $e){
    //         Log::error('Subscription DB error', ['error' => $e->getMessage()]);

    //         return response()->json([
    //             'status'      => false,
    //             'status_code' => 500,
    //             'message'     => 'Something went wrong while applying the subscription.',
    //         ], 500);
    //     }
    
    // }
}
