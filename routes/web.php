<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use  App\Http\Controllers\API\CheckoutController;
use App\Http\Controllers\Admin\{
    AuthController,
    DashboardController,
    PagesController,
    ParentController,
    ChildController,
    RecipeController,
    RecipeToolController,
    StoryController,
    GrowingActivityController,
    MonthlyChallengeController,
    CategoryController,
    AgeGroupController,
    ProductCategoryController,
    VendorController,
    SubscriptionController,SchoolController
};
use App\Http\Controllers\Vendor\{VendorAuthController,ProductController};
use App\Http\Controllers\{HomeController,PaymentController,OrderController};

/* --- cache clear: local only (never expose in production) --- */
if (app()->environment('local')) {
    Route::get('/clear', function () {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        Artisan::call('optimize:clear');

        return 'Cleared!';
    });
}

// account delete url
Route::match(['get','post'], 'delete-account', [HomeController::class, 'deleteAccount'])->name('account.deleted');




Route::get('/payment-success', [CheckoutController::class, 'paymentSuccess']);
Route::get('/payment-cancel', [CheckoutController::class, 'paymentCancel']);



// purchase subscription (requires signed URL from authenticated API: GET checkout/subscription-url)
Route::get('/purchase-plan', [PaymentController::class, 'purchasePlan'])
    ->middleware('signed')
    ->name('checkout.purchase-plan');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/cancel', [PaymentController::class, 'cancel'])->name('payment.cancel');

// product orders (requires signed URL from authenticated API: GET checkout/product-order-url)
Route::get('orders', [OrderController::class, 'orders'])
    ->middleware('signed')
    ->name('checkout.product-order');
Route::get('/orders/success', [OrderController::class, 'success'])->name('orders.success');
Route::get('/orders/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

/* ---------------------------- ADMIN PANEL ROUTES ------------------------------*/

// login (throttle POSTs against credential stuffing)
Route::get('/administrator', [AuthController::class, 'Login'])->name('admin.login');
Route::post('/administrator', [AuthController::class, 'Login'])->middleware('throttle:5,1');
Route::get('school-admin', [SchoolController::class, 'schoolAdminLogin'])->name('school.login');
Route::post('school-admin', [SchoolController::class, 'schoolAdminLogin'])->middleware('throttle:5,1');

/* -------------------------- IMPLEMENT MIDDLEWARE ----------------------*/
Route::middleware(['adminAuth'])->prefix('admin')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('logout', [AuthController::class, 'logout'])->name('admin.logout');
    Route::match(['get', 'post'], 'profile', [AuthController::class, 'viewProfile'])->name('admin.profile');
    Route::match(['get', 'post'], 'change-password', [AuthController::class, 'changePassword'])->name('admin.changepassword');
    Route::get('openai-api-key', [AuthController::class, 'getOpenAiApiKey'])->name('get.openai.api.key');
    Route::post('save-openai-api-key', [AuthController::class, 'storeOpenAiApiKey'])->name('store.openai.api.key');
    Route::get('stripe-keys', [AuthController::class, 'getStripeKeys'])->name('get.stripe.keys');
    Route::post('save-stripe-keys', [AuthController::class, 'storeStripeKeys'])->name('store.stripe.keys');
    Route::post('/check_current_password', [AuthController::class, 'CheckCurrentPassword']);
    Route::post('/upload-summernote-image', [AuthController::class, 'uploadSummernoteImage'])->name('upload.summernote.image');
    
    // parent routes
    Route::resource('parent', ParentController::class)->except('destroy');
    Route::post('parent-delete/{id}', [ParentController::class, 'destroy']);

    // kid routes
    Route::resource('kid', ChildController::class)->except('destroy');
    Route::post('kid-delete/{id}', [ChildController::class, 'destroy']);
    
    // age group
    Route::resource('age-group', AgeGroupController::class)->except('destroy');
    Route::post('age-group-delete/{id}', [AgeGroupController::class, 'destroy']);

    // pages
    Route::match(['get', 'post'], 'help-&-support', [PagesController::class, 'helpAndSupport'])->name('admin.help_&_support');
    Route::match(['get', 'post'], 'about-us/{type?}', [PagesController::class, 'aboutUs'])->name('admin.aboutUs');
    
    // t&C
    Route::match(['get', 'post'], 'terms-and-condition/{id?}',[PagesController::class, 'termsAndCondition'])->name('admin.terms_&_condition');
    Route::match(['get', 'post'], 'privacy-policy/{id?}',[PagesController::class, 'privacyPolicy'])->name('admin.privacy.policy');

    // recipe
    Route::resource('recipe', RecipeController::class)->except('destroy');
    Route::get('popular-recipes', [RecipeController::class, 'popularRecipe'])->name('popular.recipes');
    Route::get('recipe-of-the-month', [RecipeController::class, 'RecipeOfTheMonth'])->name('rotm');
    Route::resource('recipe-tool', RecipeToolController::class)->except('destroy');
    Route::post('recipe-tool-delete/{id}', [RecipeToolController::class, 'destroy']);
    Route::post('recipe-delete/{id}', [RecipeController::class, 'destroy']);
    Route::post('update-status', [RecipeController::class, 'updatePopularRecipeStatus']);
    Route::post('update-recipeoftheweek-status', [RecipeController::class, 'updateRecipeOftheWeekStatus']);
    Route::get('/get-current-recipe-of-the-week', [RecipeController::class, 'getCurrentRecipeOfTheWeek']);
    Route::get('/check-popular-recipe-count', function () {
        $popularRecipeCount = \App\Models\Recipe::whereStatus(1)->count();
        return response()->json($popularRecipeCount);
    });

    // story
    Route::resource('story', StoryController::class)->except('destroy');
    Route::get('featured-stories', [StoryController::class, 'featuredStory'])->name('featured.stories');
    Route::get('best-from-the-champ', [StoryController::class, 'BestFromTheChamp'])->name('bftc.stories');
    Route::post('story-delete/{id}', [StoryController::class, 'destroy']);
    Route::post('update-best-from-the-champs-status', [StoryController::class, 'updateBestFromtheChampStatus']);
    Route::post('featured-stories-status', [StoryController::class, 'updateFeaturedStoryStatus']);
    
    // growing activity — POST-only save paths (avoids LiteSpeed blocking PUT on /growing-activity/{id})
    Route::resource('growing-activity', GrowingActivityController::class)->except(['destroy', 'update']);
    Route::post('save-growing-activity/{id}', [GrowingActivityController::class, 'update'])->name('growing-activity.update');
    Route::post('growing-activity-delete/{id}', [GrowingActivityController::class, 'destroy']);
    Route::post('update-growing-activity-status', [GrowingActivityController::class, 'updateGrowingActivityStatus']);
    
    // monthly challenge
    Route::resource('monthly-challenge', MonthlyChallengeController::class)->except('destroy');
    Route::post('monthly-challenge-delete/{id}', [MonthlyChallengeController::class, 'destroy']);
    Route::post('update-monthlyChallenge-status', [MonthlyChallengeController::class, 'updateMonthlyChallengeStatus']);
    Route::get('/get-current-monthly-challenge', [MonthlyChallengeController::class, 'getCurrentMonthlyChallenge']);
    
    // Categories
    Route::resource('categories', CategoryController::class)->except('destroy');
    Route::post('category-delete/{id}', [CategoryController::class, 'destroy']);
    
    // product category
    Route::resource('product-categories', ProductCategoryController::class)->except('destroy');
    Route::post('product-categories-delete/{id}', [ProductCategoryController::class, 'destroy']);
    
    // vendor
    Route::resource('vendors', VendorController::class)->except('destroy');
    Route::post('vendors-delete/{id}', [VendorController::class, 'destroy'])->name('vendor.delete');
    //  all orderin admin pannel  
    Route::get('get-order',[VendorController::class,'getOrder'])->name('get.Order');
    Route::get('view-order/{id}',[VendorController::class,'viewOrder'])->name('view.order');
    Route::post('order-status-update', [VendorController::class, 'updateStatus'])->name('order.status.update');
    
    
    // vendor pannel orderlist
    Route::get('tax-value',[VendorController::class,'getTax'])->name('get.tax');
    Route::post('update-tax',[VendorController::class,'storeTax'])->name('update.tax');
    Route::get('platform-fee',[VendorController::class,'getPlatformFee'])->name('get.platformfee');
    Route::post('save-platform-fee',[VendorController::class,'storePlatformCharge'])->name('store.platformfee');
    Route::get('shipping-fee',[VendorController::class,'getShippingFee'])->name('get.shippingfee');
    Route::post('save-shipping-fee',[VendorController::class,'storeShippingFee'])->name('store.shippingfee');
    
    
    
    
    // subscription
    Route::get('subscription-purchases', [SubscriptionController::class, 'purchases'])->name('subscription.purchases');
    Route::resource('subscription', SubscriptionController::class)->except('destroy');
    Route::post('subscription/delete/{id}', [SubscriptionController::class, 'destroySubscription']);
 
    //School 
    Route::get('get-school',[SchoolController::class,'getSchool'])->name('admin.get-school');
    Route::get('add-school',[SchoolController::class,'addSchool'])->name('admin.add-school');
    Route::post('store-school',[SchoolController::class,'storeSchool'])->name('admin.store-school');
    Route::post('change-status/{id}',[SchoolController::class,'changeStatus'])->name('change-status');
    Route::delete('school/{id}', [SchoolController::class, 'destroy'])->name('admin.school.delete');
    Route::get('edit-school/{id}', [SchoolController::class, 'editSchool'])->name('edit-school');
    Route::post('edit-school/{id}',[SchoolController::class,'storeSchoolData'])->name('school.update');
    Route::get('view-school/{id}', [SchoolController::class, 'viewSchool'])->name('view.school');
});

// admin products
Route::middleware(['adminAuth'])->prefix('admin')->as('admin.')->group(function () {
    Route::resource('products', ProductController::class);
    Route::post('update-product-status', [ProductController::class, 'updateProductStatus'])->name('update-product-status');
});

// vendor products
Route::middleware(['vendorAuth'])->prefix('vendor')->as('vendor.')->group(function () {
    Route::resource('products', ProductController::class);
    Route::post('update-product-status', [ProductController::class, 'updateProductStatus'])->name('update-product-status');
    Route::get('order-list', [ProductController::class, 'orderList'])->name('order-list');
});

// school routes in school admin pannel
Route::middleware(['school'])->group(function () {
    Route::get('school-dashboard',[SchoolController::class,'schoolDashboard'])->name('school.dashboard');
    Route::get('school-logout', [SchoolController::class, 'schoolLogout'])->name('school.logout');
    Route::get('get-video', [SchoolController::class, 'getVideo'])->name('get.video');
    Route::get('add-video', [SchoolController::class, 'addVideo'])->name('add.video');
    Route::post('store-video', [SchoolController::class, 'storeVideo'])->name('store.videos');
    Route::get('edit-video/{id}', [SchoolController::class, 'edit'])->name('school.videos.edit');
    Route::post('store-edit-video/{id}', [SchoolController::class, 'update'])->name('school.videos.update');
    Route::post('school-videos/delete/{id}', [SchoolController::class, 'destroyVideo'])->name('school.videos.delete');
    Route::post('school/upload-summernote-image', [SchoolController::class, 'uploadSummernoteImage'])->name('school.upload.summernote.image');
    Route::get('change-password',[SchoolController::class,'changePassword'])->name('schooladmin-changepassword');
    Route::post('change-password',[SchoolController::class,'storeChangePassword'])->name('store-changepassword');
});

// PASSWORD set of school admin
Route::get('/reset-password/{token}', [SchoolController::class, 'create'])->name('password.reset');
Route::post('/reset-password', [SchoolController::class, 'store'])->name('password.update');

/* ---------------------------- VENDOR ROUTES ------------------------- */
Route::get('/vendor/login', [VendorAuthController::class, 'vendorLogin'])->name('vendor.login');
Route::post('/vendor/login', [VendorAuthController::class, 'vendorLogin'])->middleware('throttle:5,1');

Route::middleware(['vendorAuth'])->group(function() {
    Route::get('vendor/dashboard', [VendorAuthController::class, 'vendorDashboard'])->name('vendor.dashboard');
    Route::get('vendor/logout', [VendorAuthController::class, 'vendorLogout'])->name('vendor.logout');
});
