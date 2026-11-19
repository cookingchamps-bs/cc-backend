<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\{Subscription,Kid,UserSubscription};
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use App\Models\AddToCart;
use App\Models\OrderItem;

use App\Models\User;
use App\Services\StripeCheckoutCustomer;
use Stripe\Stripe;
use Illuminate\Support\Facades\DB;
use Stripe\Checkout\Session ;

class CheckoutController extends Controller
{

    public function subscriptionCheckoutUrl(Request $request)
    {
        $validated = $request->validate([
            'subscription_id' => 'required|string',
            'kid_id' => 'nullable',
        ]);

        $user = $request->user();

        $subscription = Subscription::find($validated['subscription_id'])
            ?? Subscription::where('stripe_price_id', $validated['subscription_id'])->first();

        if (! $subscription) {
            return response()->json([
                'status' => false,
                'status_code' => 404,
                'message' => 'Subscription not found!',
            ], 404);
        }

        $kidId = $validated['kid_id'] ?? null;
        if ($kidId === '' || $kidId === '0' || $kidId === 'null') {
            $kidId = null;
        }

        if (! empty($kidId)) {
            $kid = Kid::where('id', $kidId)->where('parent_id', $user->id)->first();
            if (! $kid) {
                return response()->json([
                    'status' => false,
                    'status_code' => 403,
                    'message' => 'This child profile does not belong to your account.',
                ], 403);
            }
        }

        $params = [
            'user_id' => $user->id,
            'subscription_id' => $subscription->id,
        ];
        if (! empty($kidId)) {
            $params['kid_id'] = $kidId;
        }

        $checkoutUrl = URL::temporarySignedRoute(
            'checkout.purchase-plan',
            now()->addMinutes(30),
            $params
        );

        return Helper::responseFunction(true, 200, 'Checkout URL created.', ['checkout_url' => $checkoutUrl], 200);
    }

    public function orders(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $addressId = $request->shipping_address_id;

        if (! $addressId) {
            return response()->json([
                'status' => false,
                'status_code' => 422,
                'message' => 'Shipping address id field is required!',
            ], 422);
        }

        $address = Address::where('id', $addressId)
            ->where('parent_id', $userId)
            ->first();
    
        if (!$address) {
            return response()->json([
                'status' => false,
                'status_code' => 403,
                'message' => 'Shipping address not valid.',
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
            return response()->json([
                'status' => false,
                'message' => 'Cart is empty!'
            ], 404);
        }
    
        // ðŸ”¥ SAME GROUP LOGIC
        $groupedByProducts = $cartItems->groupBy('product_id')->map(function ($items) {
            $first = $items->first();
    
            $totalQty = $items->sum('quantity');
            $totalPrice = $items->sum(function($item) {
                return $item->quantity * $item->price;
            });
    
            return [
                'product_id'  => $first->product_id,
                'product_name'=> $first->product->title ?? '',
                'price'       => $first->price,
                'quantity'    => $totalQty,
                'total_price' => $totalPrice,
            ];
        })->values();
    
        $totalAmount = $groupedByProducts->sum('total_price');
    
        // ðŸ”¥ SAME TAX LOGIC
        $taxAmt = Helper::getPlatformCharges();
    
        $taxValue = ($taxAmt['tax_amount'] ?? 0) > 0
            ? $taxAmt['tax_amount']
            : ($totalAmount * ($taxAmt['tax_amount_per'] ?? 0)) / 100;
    
        $tax = $taxValue
             + ($taxAmt['platform_fee'] ?? 0)
             + ($taxAmt['shipping_charges'] ?? 0);
    
        // ðŸ”¥ STRIPE ITEMS SAME
        $lineItems = [];
    
        foreach ($groupedByProducts as $item) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'AUD',
                    'product_data' => [
                        'name' => $item['product_name'],
                    ],
                    'unit_amount' => $item['price'] * 100,
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
    
        try {
            $checkoutSession = Session::create(array_merge([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
    
                // âœ… IMPORTANT (API FLOW)
                'success_url' => url('/api/payment-success-cart') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => url('/api/payment-cancel-cart'),
    
                // âœ… IMPORTANT (DATA STORE)
                'metadata' => [
                    'user_id' => $userId,
                    'address_id' => $addressId,
                    'total_amount' => $totalAmount,
                    'tax_amount' => $tax,
                ],
            ], StripeCheckoutCustomer::sessionParams($user, $address)));
    
            return response()->json([
                'status' => true,
                'checkout_url' => $checkoutSession->url,
            ]);
    
        } catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function success(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return response($this->paymentResultHtml(false, 'Session ID missing'), 400)
                ->header('Content-Type', 'text/html');
        }

        $result = $this->fulfillCartOrderFromSession($sessionId);

        return response(
            $this->paymentResultHtml(
                (bool) ($result['status'] ?? false),
                $result['message'] ?? 'Unable to confirm payment.'
            ),
            $result['http_code'] ?? 200
        )->header('Content-Type', 'text/html');
    }

    /**
     * Authenticated confirm after Stripe WebView redirect.
     * Idempotent: safe to call even if success URL already created the order.
     */
    public function confirmCartPayment(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $result = $this->fulfillCartOrderFromSession(
            $validated['session_id'],
            $request->user()?->id
        );

        $httpCode = $result['http_code'] ?? 200;

        return response()->json([
            'status' => (bool) ($result['status'] ?? false),
            'status_code' => $httpCode,
            'message' => $result['message'] ?? '',
            'data' => $result['data'] ?? null,
        ], $httpCode >= 400 ? $httpCode : 200);
    }

    public function cancel()
    {
        return response($this->paymentResultHtml(false, 'Payment cancelled.'))
            ->header('Content-Type', 'text/html');
    }

    /**
     * Create order + clear cart from a paid Stripe Checkout session.
     * Returns: status, message, http_code, data?
     */
    private function fulfillCartOrderFromSession(string $sessionId, ?int $authUserId = null): array
    {
        $existingOrder = Order::where('transaction_id', $sessionId)->first();
        if ($existingOrder) {
            if ($authUserId !== null && (int) $existingOrder->user_id !== (int) $authUserId) {
                return [
                    'status' => false,
                    'message' => 'Payment session does not belong to this user.',
                    'http_code' => 403,
                ];
            }

            // Ensure cart is cleared even if a previous run created the order but failed mid-delete.
            AddToCart::where('parent_id', $existingOrder->user_id)->delete();

            return [
                'status' => true,
                'message' => 'Order placed successfully.',
                'http_code' => 200,
                'data' => [
                    'order_id' => $existingOrder->id,
                    'unique_id' => $existingOrder->unique_id,
                ],
            ];
        }

        Stripe::setApiKey(Helper::getStripeSecret());

        DB::beginTransaction();

        try {
            $stripeSession = Session::retrieve($sessionId);

            $paymentStatus = $stripeSession->payment_status ?? '';
            if (! in_array($paymentStatus, ['paid', 'no_payment_required'], true)) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'Payment not completed.',
                    'http_code' => 402,
                ];
            }

            $userId = (int) ($stripeSession->metadata->user_id ?? 0);
            $addressId = (int) ($stripeSession->metadata->address_id ?? 0);
            $totalAmt = $stripeSession->metadata->total_amount ?? 0;
            $taxAmt = $stripeSession->metadata->tax_amount ?? 0;

            if ($authUserId !== null && $userId !== $authUserId) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'Payment session does not belong to this user.',
                    'http_code' => 403,
                ];
            }

            $user = User::find($userId);
            if (! $user) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'User not found',
                    'http_code' => 404,
                ];
            }

            if (! $addressId || ! Address::where('id', $addressId)->where('parent_id', $userId)->exists()) {
                DB::rollBack();
                return [
                    'status' => false,
                    'message' => 'Shipping address not valid.',
                    'http_code' => 422,
                ];
            }

            $paymentIntentId = $stripeSession->payment_intent;
            if (is_object($paymentIntentId)) {
                $paymentIntentId = $paymentIntentId->id ?? null;
            }
            $paymentIntentId = $paymentIntentId ? (string) $paymentIntentId : null;

            $cartQuery = AddToCart::query()->where('parent_id', $userId);
            if ($user->sub_role === 'individual') {
                $cartQuery->where('role', $user->role);
            }
            $cartItems = $cartQuery->with('product')->get();

            $groupedByProducts = $cartItems->groupBy('product_id')->map(function ($items) {
                $first = $items->first();

                $totalQty = $items->sum('quantity');
                $totalPrice = $items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });

                return [
                    'product_id' => $first->product_id,
                    'product_name' => $first->product->title ?? '',
                    'price' => $first->price,
                    'quantity' => $totalQty,
                    'total_price' => $totalPrice,
                ];
            })->values();

            $orders = Order::create([
                'user_id' => $userId,
                'role' => $user->role,
                'sub_role' => $user->sub_role ?? null,
                'total_amount' => $totalAmt,
                'tax_amount' => $taxAmt,
                'status' => 1,
                'transaction_id' => $sessionId,
                'payment_id' => $paymentIntentId,
                'shipping_address_id' => $addressId,
            ]);

            $orders->update([
                'unique_id' => '#' . str_pad($orders->id, 8, '0', STR_PAD_LEFT),
            ]);

            foreach ($groupedByProducts as $val) {
                OrderItem::create([
                    'order_id' => $orders->id,
                    'product_id' => $val['product_id'],
                    'product_name' => $val['product_name'],
                    'price' => $val['price'],
                    'quantity' => $val['quantity'],
                    'subtotal' => $val['total_price'],
                ]);
            }

            $cartClear = AddToCart::query()->where('parent_id', $userId);
            if ($user->sub_role === 'individual') {
                $cartClear->where('role', $user->role);
            }
            $cartClear->delete();

            DB::commit();

            return [
                'status' => true,
                'message' => 'Order placed successfully.',
                'http_code' => 201,
                'data' => [
                    'order_id' => $orders->id,
                    'unique_id' => $orders->unique_id,
                ],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Cart order fulfillment failed: ' . $e->getMessage(), [
                'session_id' => $sessionId,
            ]);

            return [
                'status' => false,
                'message' => 'Unable to confirm payment.',
                'http_code' => 500,
            ];
        }
    }

    private function paymentResultHtml(bool $success, string $message): string
    {
        $title = $success ? 'Payment Successful' : 'Payment Cancelled';
        $color = $success ? '#16a34a' : '#dc2626';
        $marker = $success ? 'ORDER_CONFIRMED' : 'ORDER_FAILED';

        return '<!DOCTYPE html><html><head><meta charset="utf-8">'
            . '<meta name="viewport" content="width=device-width, initial-scale=1">'
            . '<title>' . e($title) . '</title></head>'
            . '<body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;">'
            . '<div style="text-align:center;padding:24px;">'
            . '<h1 style="color:' . $color . ';">' . e($title) . '</h1>'
            . '<p>' . e($message) . '</p>'
            . '<p id="order-status" data-status="' . e($marker) . '" style="display:none;">' . e($marker) . '</p>'
            . '</div></body></html>';
    }
    
    
    public function purchasePlan(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;
        $kidId = $request->kid_id;
        $subscriptionId = $request->subscription_id;

        if ($kidId === '' || $kidId === '0' || $kidId === 'null') {
            $kidId = null;
        }

        if (! $subscriptionId) {
            return response()->json([
                'status' => false,
                'message' => 'Subscription id is required!',
            ], 422);
        }

        if (! empty($kidId)) {
            $kid = Kid::where('id', $kidId)->where('parent_id', $userId)->first();
            if (! $kid) {
                return response()->json([
                    'status' => false,
                    'message' => 'Kid not found or does not belong to this account.',
                ], 403);
            }
        }

        $subscription = Subscription::find($subscriptionId)
            ?? Subscription::where('stripe_price_id', $subscriptionId)->first();
        if (!$subscription) {
            return response()->json([
                'status' => false,
                'message' => 'Subscription not found!'
            ], 404);
        }
    
        Stripe::setApiKey(Helper::getStripeSecret());
    
        try {
            $savedAddress = Address::where('parent_id', $userId)->latest('id')->first();

            $session = Session::create(array_merge([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
    
                'line_items' => [[
                    'price' => $subscription->stripe_price_id,
                    'quantity' => 1,
                ]],
    
                // 🔥 IMPORTANT (metadata instead of session)
                'metadata' => [
                    'user_id' => $userId,
                    'kid_id' => $kidId,
                    'price_id' => $subscription->stripe_price_id,
                ],
    
                // 🔥 IMPORTANT (API flow)
                'success_url' => url('/api/payment-success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => url('/api/payment-cancel'),
            ], StripeCheckoutCustomer::sessionParams($user, $savedAddress)));
    
            return response()->json([
                'status' => true,
                'checkout_url' => $session->url,
                'data' => [
                    'checkout_url' => $session->url,
                ],
            ]);
    
        } catch (\Exception $e) {
            Log::error('Stripe Error', ['error' => $e->getMessage()]);
    
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
}

    public function successPlan(Request $request)
    {
        $sessionId = $request->get('session_id');

        if (!$sessionId) {
            return response()->json([
                'status' => false,
                'message' => 'Session ID missing'
            ], 400);
        }

        $result = $this->fulfillPlanFromSession($sessionId);
        $httpCode = $result['http_code'] ?? 200;

        return response()->json([
            'status' => (bool) ($result['status'] ?? false),
            'message' => $result['message'] ?? '',
            'data' => $result['data'] ?? null,
        ], $httpCode >= 400 ? $httpCode : 200);
    }

    /**
     * Authenticated confirm after Stripe WebView redirect for kid/parent plans.
     * Idempotent: safe if payment-success already created the subscription.
     */
    public function confirmPlanPayment(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $result = $this->fulfillPlanFromSession(
            $validated['session_id'],
            $request->user()?->id
        );

        $httpCode = $result['http_code'] ?? 200;

        return response()->json([
            'status' => (bool) ($result['status'] ?? false),
            'status_code' => $httpCode,
            'message' => $result['message'] ?? '',
            'data' => $result['data'] ?? null,
        ], $httpCode >= 400 ? $httpCode : 200);
    }

    /**
     * Create UserSubscription from a paid Stripe Checkout session.
     */
    private function fulfillPlanFromSession(string $sessionId, ?int $authUserId = null): array
    {
        $existing = UserSubscription::where('transaction_id', $sessionId)->first();
        if ($existing) {
            return [
                'status' => true,
                'message' => 'Subscription already activated.',
                'http_code' => 200,
                'data' => ['subscription_id' => $existing->id],
            ];
        }

        Stripe::setApiKey(Helper::getStripeSecret());

        try {
            $stripeSession = Session::retrieve($sessionId);

            if (($stripeSession->payment_status ?? null) !== 'paid' &&
                ($stripeSession->status ?? null) !== 'complete') {
                return [
                    'status' => false,
                    'message' => 'Payment not completed yet.',
                    'http_code' => 402,
                ];
            }

            $userId = $stripeSession->metadata->user_id ?? null;
            $kidId = $stripeSession->metadata->kid_id ?? null;
            $priceId = $stripeSession->metadata->price_id ?? null;

            if ($authUserId && $userId && (int) $userId !== (int) $authUserId) {
                return [
                    'status' => false,
                    'message' => 'Session does not belong to this user.',
                    'http_code' => 403,
                ];
            }

            $subscription = $priceId
                ? Subscription::where('stripe_price_id', $priceId)->first()
                : null;
            $user = $userId ? User::find($userId) : null;

            if (!$subscription || !$user) {
                return [
                    'status' => false,
                    'message' => 'Invalid user or subscription',
                    'http_code' => 404,
                ];
            }

            // Treat empty / "0" / "null" kid ids as parent-level purchase.
            if ($kidId === '' || $kidId === '0' || $kidId === 'null') {
                $kidId = null;
            }

            $record = UserSubscription::create([
                'user_id'            => $userId,
                'kid_id'             => $kidId,
                'subscription_id'    => $subscription->id,
                'role'               => 'parent',
                'sub_role'           => $user->sub_role === 'individual' ? 'individual' : null,
                'transaction_id'     => $sessionId,
                'stripe_product_id'  => $subscription->stripe_product_id,
                'stripe_price_id'    => $priceId,
                'payment_status'     => 1,
                'start_date'         => now(),
                'end_date'           => null,
                'is_active'          => 1,
            ]);

            return [
                'status' => true,
                'message' => 'Subscription purchased successfully!',
                'http_code' => 200,
                'data' => ['subscription_id' => $record->id],
            ];
        } catch (\Exception $e) {
            Log::error('Subscription Error', ['error' => $e->getMessage()]);

            // Race: another request may have created it after our existence check.
            $existing = UserSubscription::where('transaction_id', $sessionId)->first();
            if ($existing) {
                return [
                    'status' => true,
                    'message' => 'Subscription already activated.',
                    'http_code' => 200,
                    'data' => ['subscription_id' => $existing->id],
                ];
            }

            return [
                'status' => false,
                'message' => 'Something went wrong',
                'http_code' => 500,
            ];
        }
    }
    
    public function cancelPlan()
    {
        return response()->json([
            'status' => false,
            'message' => 'Payment cancelled.'
        ], 422);
    }
    
    


            
        
    
}
