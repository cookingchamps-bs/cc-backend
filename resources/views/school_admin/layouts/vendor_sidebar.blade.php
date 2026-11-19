@php
    $panel = request()->is('admin/*') ? 'admin' : 'vendor';
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4"> 
    <div class="d-flex">
        <a href="{{ route('vendor.dashboard')}}" class="brand-link"> 
            <img style="width: 100px; height: 60px" src={{ asset('storage/vendor_image.jpg')}} alt="Vendor Logo" class="brand-image "> 
            <span class="brand-text font-weight-bold"><b>Vendor</b></span>
        </a>
        {{-- <!--<a href="{{ url('admin/dashboard')}}" class="brand-link"> -->
        <!--    <img src={{ asset(Auth::guard('admin')->user()->image)}} alt="MarketMaker Logo" height="30px" style="text-center; background-color:white;"> -->
        <!--    <span class="brand-text font-weight-bold"><b></b></span> -->
        <!--</a>--> --}}
    </div>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item"> 
                    <a href="{{ route('vendor.dashboard')}}" class="nav-link {{ Request::is('vendor/dashboard') ? 'active' : '' }}"> <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p> Dashboard </p>
                    </a>
                </li>
                <li class="nav-item"> 
                    <a href="{{ route($panel .'.products.index')}}" class="nav-link {{ Request::is('vendor/products') ? 'active' : '' }}"><i class="fa-solid fa-cart-shopping"></i>&nbsp;
                        <p> Shop </p>
                    </a> 
                </li>
                <li class="nav-item"> 
                    <a href="{{ route('vendor.logout')}}" class="nav-link confirm-logout"><i class="fa-solid fa-right-from-bracket"></i>&nbsp;
                        <p> Logout </p>
                    </a> 
                </li>
            </ul>
        </nav>
    </div>
</aside>
@push('script')
 <script>
     $(document).ready(function(){
        $('.confirm-logout').click(function(event){
            event.preventDefault();
            var logoutUrl = $(this).attr('href');
            
            if(confirm("Are you sure you want to logout?")){
                window.location.href = logoutUrl; // Redirect to the logout URL
            }
        });
    });
 </script>
  @endpush
