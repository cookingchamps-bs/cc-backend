<aside class="main-sidebar sidebar-dark-primary elevation-4">

 <div class="d-flex">

            <a href="{{ url('admin/dashboard')}}" class="brand-link">
                @if(Auth::guard('admin')->check())
                <img src="{{ asset(Auth::guard('admin')->user()->image ?: 'storage/logo.png') }}" alt="CookingChamps Logo" class="brand-image">
                @else
                <img src="{{ asset('storage/logo.png') }}" alt="CookingChamps Logo" class="brand-image">
                @endif
            </a>
        </div>

@if(Auth::guard('admin')->check())
@php
    $panel = request()->is('admin/*') ? 'admin' : 'vendor';

    $isOrders = Request::is('admin/get-order') || Request::is('admin/view-order*');

    $isUsers = Request::is('admin/parent*')
        || Request::is('admin/kid*')
        || Request::is('admin/vendors*')
        || Request::is('admin/get-school*')
        || Request::is('admin/add-school*')
        || Request::is('admin/edit-school*')
        || Request::is('admin/view-school*');

    $isProducts = Request::is('admin/product-categories*') || Request::is('admin/products*');

    $isStories = Request::is('admin/story*')
        || Request::is('admin/featured-stories*')
        || Request::is('admin/best-from-the-champ*');

    $isRecipes = request()->routeIs('recipe.*')
        || Request::is('admin/popular-recipes*')
        || Request::is('admin/recipe-of-the-month*');

    $isLibrary = Request::is('admin/recipe-tool*')
        || Request::is('admin/categories*')
        || Request::is('admin/age-group*');

    $isPromotions = Request::is('admin/growing-activity*')
        || Request::is('admin/monthly-challenge*');

    $isPages = Request::is('admin/about-us*');

    $isSubscription = Request::is('admin/subscription*')
        || Request::is('admin/subscription-purchases*');

    $isFees = Request::is('admin/tax-value*')
        || Request::is('admin/platform-fee*')
        || Request::is('admin/shipping-fee*');

    $isSettings = Request::is('admin/profile*')
        || Request::is('admin/change-password*')
        || Request::is('admin/openai-api-key*')
        || Request::is('admin/stripe-keys*')
        || Request::is('admin/help-&-support*')
        || Request::is('admin/privacy-policy*')
        || Request::is('admin/terms-and-condition*');
@endphp
<div class="sidebar">
<nav class="mt-2">
<ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

    {{-- 1. Dashboard --}}
    <li class="nav-item">
        <a href="{{ url('admin/dashboard')}}" class="nav-link {{ Request::is('admin/dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>Dashboard</p>
        </a>
    </li>

    {{-- 2. Orders --}}
    <li class="nav-item">
        <a href="{{ route('get.Order') }}" class="nav-link {{ $isOrders ? 'active' : '' }}">
            <i class="nav-icon fas fa-shopping-cart"></i>
            <p>Orders</p>
        </a>
    </li>

    {{-- 3. Users --}}
    <li class="nav-item has-treeview {{ $isUsers ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isUsers ? 'active' : '' }}">
            <i class="nav-icon fas fa-users"></i>
            <p>
                Users
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('parent.index') }}" class="nav-link {{ Request::is('admin/parent*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Parents</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('kid.index') }}" class="nav-link {{ Request::is('admin/kid*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Kids</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('vendors.index') }}" class="nav-link {{ Request::is('admin/vendors*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Vendors</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.get-school') }}" class="nav-link {{ Request::is('admin/get-school*') || Request::is('admin/add-school*') || Request::is('admin/edit-school*') || Request::is('admin/view-school*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Schools</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 4. Products (shop) --}}
    <li class="nav-item has-treeview {{ $isProducts ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isProducts ? 'active' : '' }}">
            <i class="nav-icon fas fa-box"></i>
            <p>
                Products
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('product-categories.index') }}" class="nav-link {{ Request::is('admin/product-categories*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Product categories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route($panel . '.products.index') }}" class="nav-link {{ Request::is('admin/products*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Products</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 5. Stories --}}
    <li class="nav-item has-treeview {{ $isStories ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isStories ? 'active' : '' }}">
            <i class="nav-icon fas fa-newspaper"></i>
            <p>
                Stories
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('story.index') }}" class="nav-link {{ request()->routeIs('story.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All stories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('featured.stories') }}" class="nav-link {{ Request::is('admin/featured-stories*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Featured stories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('bftc.stories') }}" class="nav-link {{ Request::is('admin/best-from-the-champ*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Best from the Champs</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 6. Recipes --}}
    <li class="nav-item has-treeview {{ $isRecipes ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isRecipes ? 'active' : '' }}">
            <i class="nav-icon fas fa-utensils"></i>
            <p>
                Recipes
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('recipe.index') }}" class="nav-link {{ request()->routeIs('recipe.*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>All recipes</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('popular.recipes') }}" class="nav-link {{ Request::is('admin/popular-recipes*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Popular recipes</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('rotm') }}" class="nav-link {{ Request::is('admin/recipe-of-the-month*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Recipe of the month</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 7. Library --}}
    <li class="nav-item has-treeview {{ $isLibrary ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isLibrary ? 'active' : '' }}">
            <i class="nav-icon fas fa-layer-group"></i>
            <p>
                Library
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('recipe-tool.index') }}" class="nav-link {{ Request::is('admin/recipe-tool*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Recipe tools</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ Request::is('admin/categories*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Recipe categories</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('age-group.index') }}" class="nav-link {{ Request::is('admin/age-group*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Age groups</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 8. Promotions --}}
    <li class="nav-item has-treeview {{ $isPromotions ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isPromotions ? 'active' : '' }}">
            <i class="nav-icon fas fa-bullhorn"></i>
            <p>
                Promotions
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('growing-activity.index') }}" class="nav-link {{ Request::is('admin/growing-activity*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Growing activity</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('monthly-challenge.index') }}" class="nav-link {{ Request::is('admin/monthly-challenge*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Monthly challenge</p>
                </a>
            </li>
            {{-- Banners: route exists in web.php; enable when Admin\BannerController is present (was commented in legacy sidebar). --}}
        </ul>
    </li>

    {{-- 9. Pages (About) --}}
    <li class="nav-item has-treeview {{ $isPages ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isPages ? 'active' : '' }}">
            <i class="nav-icon fas fa-file-lines"></i>
            <p>
                Pages
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.aboutUs', ['type' => 'introduction-to-cookingchamps']) }}" class="nav-link {{ Request::is('admin/about-us/introduction-to-cookingchamps*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>CookingChamps intro</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.aboutUs', ['type' => 'message-for-parents']) }}" class="nav-link {{ Request::is('admin/about-us/message-for-parents*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Message for parents</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.aboutUs', ['type' => 'message-to-cookingchamps']) }}" class="nav-link {{ Request::is('admin/about-us/message-to-cookingchamps*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Message to CookingChamps</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.aboutUs', ['type' => 'cookingchamps-moto']) }}" class="nav-link {{ Request::is('admin/about-us/cookingchamps-moto*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>CookingChamps moto</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 10. Subscriptions --}}
    <li class="nav-item has-treeview {{ $isSubscription ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isSubscription ? 'active' : '' }}">
            <i class="nav-icon fas fa-credit-card"></i>
            <p>
                Subscriptions
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('subscription.purchases') }}" class="nav-link {{ Request::is('admin/subscription-purchases*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Purchases</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('subscription.index') }}" class="nav-link {{ request()->routeIs('subscription.index', 'subscription.create', 'subscription.edit') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Plans</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 11. Store fees (checkout / platform) --}}
    <li class="nav-item has-treeview {{ $isFees ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isFees ? 'active' : '' }}">
            <i class="nav-icon fas fa-money-bill-wave"></i>
            <p>
                Store fees
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('get.tax') }}" class="nav-link {{ Request::is('admin/tax-value*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Tax</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('get.platformfee') }}" class="nav-link {{ Request::is('admin/platform-fee*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Platform fee</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('get.shippingfee') }}" class="nav-link {{ Request::is('admin/shipping-fee*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Shipping</p>
                </a>
            </li>
        </ul>
    </li>

    {{-- 12. Settings --}}
    <li class="nav-item has-treeview {{ $isSettings ? 'menu-open' : '' }}">
        <a href="#" class="nav-link {{ $isSettings ? 'active' : '' }}">
            <i class="nav-icon fas fa-cog"></i>
            <p>
                Settings
                <i class="fas fa-angle-left right"></i>
            </p>
        </a>
        <ul class="nav nav-treeview">
            <li class="nav-item">
                <a href="{{ route('admin.profile') }}" class="nav-link {{ Request::is('admin/profile*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Profile</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.changepassword') }}" class="nav-link {{ Request::is('admin/change-password*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Change password</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('get.openai.api.key') }}" class="nav-link {{ Request::is('admin/openai-api-key*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>OpenAI API key</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('get.stripe.keys') }}" class="nav-link {{ Request::is('admin/stripe-keys*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Stripe keys</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.help_&_support') }}" class="nav-link {{ Request::is('admin/help-&-support*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Help & support</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.privacy.policy') }}" class="nav-link {{ Request::is('admin/privacy-policy*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Privacy policy</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.terms_&_condition') }}" class="nav-link {{ Request::is('admin/terms-and-condition*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Terms & conditions</p>
                </a>
            </li>
        </ul>
    </li>

    <li class="nav-item">
        <a href="{{ route('admin.logout') }}" class="nav-link confirm-logout">
            <i class="nav-icon fas fa-right-from-bracket"></i>
            <p>Logout</p>
        </a>
    </li>

</ul>
</nav>


</div>
@endif
</aside>


@push('script')
 <script>
     $(document).ready(function(){
        $('.confirm-logout').click(function(event){
            event.preventDefault();
            var logoutUrl = $(this).attr('href');
            Swal.fire({
                title: 'Logout?',
                text: 'Are you sure you want to logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#48335C',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Yes, logout'
            }).then(function(result){
                if(result.isConfirmed){
                    window.location.href = logoutUrl;
                }
            });
        });
    });
 </script>
  @endpush
