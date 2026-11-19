<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController as OrderPaymentController;
use App\Http\Controllers\API\{
    AuthController,
    CheckoutController,
    ChildController,
    HomeController,
    PagesApiController,
    ParentController,
    ProductController,
    OrderController,
    SchoolController,
    HomeControllerV1,
    OpenAiController
};

/* --- Public API routes behind X-CLIENT gate ---- */
Route::middleware(['api.client'])->group(function () {
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('register-parent', [AuthController::class, 'registerParent']);
        Route::post('social-login', [AuthController::class, 'socialLogin']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('otp-verify', [AuthController::class, 'otpVerify']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    Route::get('recipe-tools', [ParentController::class, 'recipeTools']);

    // pages routes
    Route::get('help-&-support', [PagesApiController::class, 'helpandSupport']);
    Route::get('about-us', [PagesApiController::class, 'aboutUs']);
    Route::get('terms-and-condition',[PagesApiController::class,'termsAndCondition']);
    Route::get('privacy-policy',[PagesApiController::class, 'privacyPolicy']);

    Route::get('home', [HomeController::class, 'home']);
    Route::get('v1/home', [HomeControllerV1::class, 'home']);
    Route::get('get-banner-description',[HomeController::class,'getBannerDescription']);
    Route::get('get-growing-description',[HomeController::class,'getGrowingDescription']);
    Route::get('monthly-challenge-detail/{id}', [HomeController::class, 'monthlyChallengeDetail']);
});

/* --------------- Implement Middleware API routes -------------- */
Route::middleware(['auth:sanctum'])->group(function () {

    // auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user-detail', [AuthController::class, 'userDetail']);
    Route::post('update-profile', [AuthController::class, 'updateProfile']);
    Route::post('change-password', [AuthController::class, 'changePassword']);
    Route::post('delete-account', [AuthController::class, 'deleteAccount']);
    Route::post('update-role', [AuthController::class, 'updateRole']);

    // add to favourites
    Route::post('add_to_favorites', [HomeController::class, 'addtoFavorites']);
    Route::get('favorites-listing', [HomeController::class, 'favoritesListing']);

    Route::get('check-kid-username', [AuthController::class, 'checkKidUsername']);

    // parent routes
    Route::post('add-stories', [ParentController::class, 'addStories']);
    Route::post('edit-stories', [ParentController::class, 'editStories']);
    Route::get('story-listing', [ParentController::class, 'storyListing']);
    Route::get('story-detail', [ParentController::class, 'storyDetail']);
    Route::post('add-recipe', [ParentController::class, 'addRecipe']);
    Route::post('edit-recipe', [ParentController::class, 'editRecipe']);
    Route::get('recipe-listing', [ParentController::class, 'recipeListing']);
    Route::get('recipe-detail', [ParentController::class, 'recipeDetail']);
    // ashish code
    Route::post('like-recipe', [ParentController::class, 'likeRecipe']);

    // friend & follow request (require auth)
    Route::post('send-friend-request', [HomeController::class, 'sendFriendRequest']);
    Route::post('send-follow-request', [HomeController::class, 'sendFollowRequest']);
    Route::get('friend-request', [HomeController::class, 'friendRequest']);
    Route::post('accept-friend-request', [HomeController::class, 'acceptFriendRequest']);
    Route::get('pending-friend-request-count', [HomeController::class, 'pendingReqCount']);
    Route::get('follow-request-list', [HomeController::class, 'followReqListing']);

    // share stories
    Route::post('share-stories', [HomeController::class, 'shareStories']);

    // notification route
    Route::get('notification-listing', [HomeController::class, 'notificationListing']);
    Route::delete('delete-notification', [HomeController::class, 'deleteNotification']);
    
    // subscription list
    Route::get('subscription-list', [HomeController::class, 'subscriptionList']);

    // vendor shops (browse — parent or kid)
    Route::post('shops', [ProductController::class, 'shops']);
    Route::get('product-detail', [ProductController::class, 'productDetail']);
    Route::get('product-categories', [ProductController::class, 'productCategory']);
    Route::post('add-to-wishlist', [ProductController::class, 'addToWishlist']);
    Route::get('get-wishlist-data', [ProductController::class, 'getWishlistData']);

    Route::middleware(['parent.user'])->group(function () {
        Route::post('register-kid', [AuthController::class, 'registerKid']);
        Route::get('my-child-listing', [ChildController::class, 'myChildListing']);
        Route::delete('delete-kid', [ChildController::class, 'deleteKid']);

        Route::post('add-address', [ProductController::class, 'addAddress']);
        Route::post('edit-address', [ProductController::class, 'editAddress']);
        Route::post('delete-address', [ProductController::class, 'deleteAddress']);
        Route::get('address-list', [ProductController::class, 'AddressList']);
        Route::post('add-to-cart', [ProductController::class, 'addToCart']);
        Route::post('remove-cart', [ProductController::class, 'removeCart']);
        Route::get('cart-list', [ProductController::class, 'cartList']);
        Route::post('update-quantity', [ProductController::class, 'updateQuantity']);

        Route::get('my-orders', [OrderController::class, 'myOrders']);
        Route::post('order-details', [OrderController::class, 'orderDetails']);
        Route::post('confirm-cart-payment', [CheckoutController::class, 'confirmCartPayment']);
        Route::post('confirm-plan-payment', [CheckoutController::class, 'confirmPlanPayment']);
        Route::get('checkout/subscription-url', [CheckoutController::class, 'subscriptionCheckoutUrl']);
        Route::post('checkout/product-order-url', [CheckoutController::class, 'orders']);
        Route::post('purchase-plan', [CheckoutController::class, 'purchasePlan']);

        Route::post('ai/recipe-from-image', [OpenAiController::class, 'recipeFromImage']);
        Route::post('ai/random-recipe', [OpenAiController::class, 'randomRecipe']);
        Route::post('ai/recipes-from-ingredients', [OpenAiController::class, 'recipesFromIngredients']);
        Route::post('ai/recipe-from-ingredients', [OpenAiController::class, 'recipeFromIngredients']);
        Route::post('ai/generate-image', [OpenAiController::class, 'generateImage']);
    });

    // school route
    Route::get('get-school', [SchoolController::class, 'getSchool']);
    Route::post('get-view', [SchoolController::class, 'addView']);

});

    //  Stripe redirect after payment (GET) — public browser redirects from Stripe
    Route::get('payment-success-cart', [CheckoutController::class, 'success']);
    Route::get('payment-cancel-cart', [CheckoutController::class, 'cancel']);
    
    
    // plan ka hai 
    Route::get('payment-success', [CheckoutController::class, 'successPlan']);
    Route::get('payment-cancel', [CheckoutController::class, 'cancelPlan']);
    
