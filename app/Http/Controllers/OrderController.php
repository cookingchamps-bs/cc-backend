<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\{DB,Log};
use App\Models\{AddToCart, Address, Order, OrderItem, User};
use App\Services\StripeCheckoutCustomer;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Checkout\Session as StripeSession;
use Carbon\Carbon;

class OrderController extends Controller
{
    // orders
    public function orders(Request $request)
    {
        
        $userId    = $request->user_id;
        $addressId = $request->shipping_address_id;
   

        if(!$userId || !$addressId){
            return response()->json(['status' => false, 'status_code' => 422, 'message' => 'User Id & shipping address id field is required!'], 422);
        }
        
        $user = User::find($userId);
        if (! $user) {
            return response()->json(['status' => false, 'status_code' => 404, 'message' => 'User not found!'], 404);
        }

        $address = Address::where('id', $addressId)->where('parent_id', $userId)->first();
        if (! $address) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Shipping address not found or does not belong to this user.',
            ], 403);
        }

        $cartItems = AddToCart::when(is_null($user->sub_role), function ($query) use ($userId) {
                $query->where('parent_id', $userId);
            })
            ->when($user->sub_role === 'individual', function ($query) use ($user) {
                $query->where('parent_id', $user->id)
                      ->where('role', $user->role);
            })
            ->with('product')
            ->get();
        
        if ($cartItems->isEmpty()) {
            return response()->json(['status' => false, 'status_code' => 404, 'message' => 'Cart is empty!'], 404);
        }
        
        // group By products
        $groupedByProducts = $cartItems->groupBy('product_id')->map(function ($items) {
                $first = $items->first();
                
                $totalQty = $items->sum('quantity');
                $totalPrice = $items->sum(function($item) {
                    return $item->quantity * $item->price;
                });
                
                
                
                return [
                    'cart_id'     => $first->id,
                    'product_id'  => $first->product_id,
                    'product_name'=> $first->product->title ?? '',
                    'image'       => $first->product->image ?? '',
                    'price'       => $first->price,
                    'quantity'    => (string) $totalQty,
                    'total_price' => (string) $totalPrice,
                ];
            })->values();
            
        $totalAmount =  $groupedByProducts->sum('total_price');
        
        
        $totalAmount = $groupedByProducts->sum('total_price');

        $taxAmt = Helper::getPlatformCharges();
        
        // tax logic
        $taxValue = ($taxAmt['tax_amount'] ?? 0) > 0
            ? $taxAmt['tax_amount']
            : ($totalAmount * ($taxAmt['tax_amount_per'] ?? 0)) / 100;
        
        $tax = $taxValue
             + ($taxAmt['platform_fee'] ?? 0)
             + ($taxAmt['shipping_charges'] ?? 0);
             
            
        // prepare line items for Stripe
        $lineItems = [];
        foreach ($groupedByProducts as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'AUD',
                    'product_data' => [
                        'name' => $item['product_name'],
                    ],
                    'unit_amount' => $item['price'] * 100, // cents
                ],
                'quantity' => $item['quantity'],
            ];
        }
        
        if ($tax > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'AUD',
                    'product_data' => [
                        'name' => 'Tax',
                    ],
                    'unit_amount' => $tax * 100,
                ],
                'quantity' => 1,
            ];
        }
        
        Stripe::setApiKey(Helper::getStripeSecret());
        
        // ----------- stripe checkout --------- //
        try{
            $checkoutSession = Session::create(array_merge([
                'payment_method_types' => ['card'],
                'line_items'           => $lineItems,
                'mode'                 => 'payment',
                'success_url'          => route('orders.success'),
                'cancel_url'           => route('orders.cancel'),
            ], StripeCheckoutCustomer::sessionParams($user, $address)));
            
            session([
                'stripe_session_id' => $checkoutSession->id,
                'user_id'           => $userId,
                'total_amount'      => $totalAmount,
                'tax_amount'        => $tax,
                'address_id'        => $addressId,
                'cart_items'        => $groupedByProducts
            ]);
            
            return redirect($checkoutSession->url);
        }catch(\Exception $e){
            return back()->withErrors(['error' => 'Stripe Error: ' . $e->getMessage()]);
        }
    }
    
    
    
    
    
    // success
    public function success()
    {
        $sessionId = session('stripe_session_id');
        $userId    = session('user_id');
        $totalAmt  = session('total_amount');
        $taxAmt    = session('tax_amount');
        $addressId = session('address_id');
        $cartItems = session('cart_items');
        
        session()->forget(['stripe_session_id', 'user_id','total_amount','tax_amount','address_id','cart_items']);
        
        // retrieve user
        $user = User::find($userId);
        
        if (!$user || !$sessionId) {
            return response()->json(['status' => false, 'message' => 'Invalid session or user.'], 400);
        }
        
        Stripe::setApiKey(Helper::getStripeSecret());
        
        DB::beginTransaction();
        
        try{
            $stripeSession = StripeSession::retrieve($sessionId);
            $paymentIntentId = $stripeSession->payment_intent;
            
            // orders
            $orders = Order::create([
                'user_id' => $userId,
                'role' => $user->role,
                'sub_role' => $user->sub_role ?? null,
                'total_amount' => $totalAmt,
                'tax_amount' => $taxAmt,
                'status' => 1,
                'transaction_id' => $sessionId,
                'payment_id' => $paymentIntentId,
                'shipping_address_id' => $addressId
            ]);
            
            $orders->update([
                'unique_id' => '#' . str_pad($orders->id, 8, '0', STR_PAD_LEFT),
            ]);
            
            // order items
            if($cartItems->isNotEmpty()){
                foreach($cartItems as $val) {
                    $orderItems = OrderItem::create([
                        'order_id' => $orders->id,
                        'product_id' => $val['product_id'],
                        'product_name' => $val['product_name'],
                        'price' => $val['price'],
                        'quantity' => $val['quantity'],
                        'subtotal' => $val['total_price']
                    ]);
                }
            }
            
            // delete cart items
            AddToCart::where('parent_id', $userId)->delete();
            DB::commit();
            
            return response()->json([
                'status'      => true,
                'status_code' => 201,
                'message'     => 'Order placed successfully.',
            ], 201);
        }catch(\Exception $e){
            DB::rollBack();
            Log::error('Stripe Session Retrieval Failed: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Unable to confirm payment.'], 500);
        }
        
    }
    
    // cancel url
    public function cancel()
    {
        session()->forget(['stripe_session_id', 'user_id','total_amount','tax_amount','address_id','cart_items']);

        return response()->json([
            'status' => false,
            'status_code' => 422,
            'message' => 'Payment failed.',
        ], 422);
    }
}

