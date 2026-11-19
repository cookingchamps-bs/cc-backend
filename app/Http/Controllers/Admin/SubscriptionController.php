<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\{Subscription,SubscriptionDetail,UserSubscription};
use App\Helpers\Helper;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Str;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $common = Helper::getCommonData('Subscription plans');
        $subscription = Subscription::all();
        
        return view('admin.subscription.index', compact('common','subscription'));
    }

    /**
     * List subscription purchases (parent / kid).
     */
    public function purchases()
    {
        $common = Helper::getCommonData('Subscription purchases');
        $purchases = UserSubscription::query()
            ->with(['user', 'kid', 'subscriptionPlan'])
            ->latest()
            ->get();

        return view('admin.subscription.purchases', compact('common', 'purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $common = Helper::getCommonData('Add Subscription', 'Add Subscription', 'Submit');

        return view('admin.subscription.create', compact('common'));
    }

    /**
     * Store a newly created resource in storage.
     * OLD SUBSCRIPTION WORKING MODEL
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'name'    => 'required',
    //         'type'    => 'required',
    //         'amount'  => 'required',
    //         // 'title'   => 'nullable|array',
    //         // 'title.*' => 'nullable|string|max:255'
    //     ]);
        
    //     try{
    //         $stripekey = config('services.stripe.STRIPE_SECRET');
            
    //         $stripe = new StripeClient($stripekey);
            
    //         // Create the product
    //         $product = $stripe->products->create([
    //             'name' => $request->name,
    //         ]);
    
    //         // Create the price for the product
    //         $price = $stripe->prices->create([
    //             'product' => $product->id,
    //             'unit_amount' => $request->amount * 100,
    //             'currency' => 'usd', 
    //             'recurring' => [
    //                 'interval' => strtolower($request->type),
    //             ],
    //         ]);
            
    //         // create subscription
    //         $subscription = Subscription::create([
    //             'name' => $request->name,
    //             'type' => $request->type,
    //             'amount' => $request->amount,
    //             'stripe_product_id' => $product->id ?? null,
    //             'stripe_price_id'   => $price->id ?? null
    //         ]);
            
    //         if($request->has('title')) {
    //             foreach($request->title as $val) {
    //                 SubscriptionDetail::create([
    //                     'subscription_id' => $subscription->id,
    //                     'title' => $val
    //                 ]);
    //             }
    //         }
            
    //         $notification = [
    //             'message' => 'Subscription Created Successfully!',
    //             'alert-type' => 'success',
    //         ];
    
    //         return Helper::redirectFunction('subscription.index', $notification);
    //     }catch (\Exception $e) {
    //         Log::error('Stripe Error: '.$e->getMessage());

    //         return back()->with(['message' => 'Something went wrong: ' . $e->getMessage()])->withInput();
    //     }
    // }
    
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required',
            'amount'  => 'required',
        ]);
        
        try{
            $stripekey = Helper::getStripeSecret();
            if (empty($stripekey)) {
                return back()->with([
                    'message' => 'Stripe secret key is not configured. Add it in Settings → Stripe keys.',
                    'alert-type' => 'error',
                ])->withInput();
            }

            $stripe = new StripeClient($stripekey);
            
            // Create the product
            $product = $stripe->products->create([
                'name' => $request->name,
            ]);
            
    
            // Create the price for the product
            $price = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $request->amount * 100,
                'currency' => 'AUD',
            ]);
            
            // create subscription
            $subscription = Subscription::create([
                'name'   => $request->name,
                'type'   => $request->filled('type')
                    ? $request->type
                    : Str::slug($request->name),
                'amount' => $request->amount,
                'stripe_product_id' => $product->id ?? null,
                'stripe_price_id'   => $price->id ?? null
            ]);
            
            $notification = [
                'message' => 'Subscription Created Successfully!',
                'alert-type' => 'success',
            ];
    
            return Helper::redirectFunction('subscription.index', $notification);
        }catch (\Exception $e) {
            Log::error('Stripe Error: '.$e->getMessage());

            return back()->with(['message' => 'Something went wrong. Please try again.', 'alert-type' => 'error'])->withInput();
        }
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
        $common = Helper::getCommonData('Edit Subscription', 'Edit Subscription', 'Update');
        $subscription = Subscription::find(decrypt($id));
        
        return view('admin.subscription.edit', compact('common', 'subscription'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'amount'  => 'required',
        ]);
        
        try{
            $subscription = Subscription::find(decrypt($id));
            
            $stripekey = Helper::getStripeSecret();
            if (empty($stripekey)) {
                return back()->with([
                    'message' => 'Stripe secret key is not configured. Add it in Settings → Stripe keys.',
                    'alert-type' => 'error',
                ])->withInput();
            }

            $stripe = new StripeClient($stripekey);
            
            // Create the product
            $product = $stripe->products->create([
                'name' => $subscription->name,
            ]);
            
            // Create the price for the product
            $price = $stripe->prices->create([
                'product' => $product->id,
                'unit_amount' => $request->amount * 100,
                'currency' => 'AUD',
            ]);
            
            // create subscription
            $subscription->update([
                'name'  => $subscription->name,
                'type'  => $request->filled('type')
                    ? $request->type
                    : ($subscription->type ?: Str::slug($subscription->name)),
                'amount' => $request->amount,
                'stripe_product_id' => $product->id,
                'stripe_price_id' => $price->id
            ]);
            
            $notification = [
                'message' => 'Subscription update successfully!',
                'alert-type' => 'success',
            ];
    
            return Helper::redirectFunction('subscription.index', $notification);
        }catch (\Exception $e) {
            Log::error('Stripe Error: '.$e->getMessage());

            return back()->with(['message' => 'Something went wrong. Please try again.', 'alert-type' => 'error'])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroySubscription(string $id)
    {
        try{
            $subscription = Subscription::find(decrypt($id));
            $subscription->delete();
            
            $notification = [
                'message' => 'Subscription deleted successfully!',
                'alert-type' => 'success',
            ];
            
            return Helper::redirectFunction('subscription.index', $notification);
        }catch(\Exception $e){
            return back()->with(['message' => 'Something went wrong. Please try again.', 'alert-type' => 'error'])->withInput();
        }
    }
}
