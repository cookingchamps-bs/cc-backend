@php
    $panel = request()->is('admin/*') ? 'admin' : 'vendor';
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <div class="d-flex">
        <a href="{{ route('vendor.dashboard')}}" class="brand-link">
            <img src="{{ asset('storage/logo.png') }}" alt="Cooking Champs" class="brand-image">
            <span class="brand-text font-weight-bold">Vendor</span>
        </a>
    </div>
    <div class="sidebar">
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('vendor.dashboard')}}" class="nav-link {{ Request::is('vendor/dashboard') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>Dashboard</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route($panel .'.products.index')}}" class="nav-link {{ Request::is('vendor/products*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-cart-shopping"></i>
                        <p>Products</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route($panel .'.order-list') }}"
                       class="nav-link {{ Request::is($panel.'/order-list*') ? 'active' : '' }}">
                        <i class="nav-icon fas fa-box"></i>
                        <p>Orders</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('vendor.logout')}}" class="nav-link confirm-logout">
                        <i class="nav-icon fas fa-right-from-bracket"></i>
                        <p>Logout</p>
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
