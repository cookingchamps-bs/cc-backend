<aside class="main-sidebar sidebar-dark-primary elevation-4">
    @if(Auth::guard('school')->check())
        <div class="d-flex">
            <a href="{{ route('school.dashboard')}}" class="brand-link">
                <img src="{{ asset('storage/logo.png') }}"
                     alt="CookingChamps Logo"
                     class="brand-image"
                     onerror="this.src='{{ url('assets/images/logocooking.png') }}'">
            </a>
        </div>

        <div class="sidebar">
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column"
                    data-widget="treeview"
                    role="menu"
                    data-accordion="false">

                    <li class="nav-item">
                        <a href="{{ route('school.dashboard') }}"
                           class="nav-link {{ Request::is('school-dashboard') || Request::is('school-admin/dashboard*') || request()->routeIs('school.dashboard') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('get.video') }}"
                           class="nav-link {{ Request::is('*video*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-video"></i>
                            <p>Videos</p>
                        </a>
                    </li>

                    <li class="nav-item has-treeview {{ Request::is('*changepassword*') || request()->routeIs('schooladmin-changepassword') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ Request::is('*changepassword*') ? 'active' : '' }}">
                            <i class="nav-icon fas fa-gear"></i>
                            <p>
                                Settings
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('schooladmin-changepassword') }}"
                                   class="nav-link {{ request()->routeIs('schooladmin-changepassword') ? 'active' : '' }}">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Change password</p>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('school.logout') }}"
                           class="nav-link confirm-logout">
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
