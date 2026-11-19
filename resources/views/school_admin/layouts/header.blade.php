<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a class="nav-link" href="javascript:;" role="button">
                Welcome
                @if(Auth::guard('school')->check())
                    <b>{{ ucwords(auth('school')->user()->school_name) }}</b>
                @endif
            </a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
        @if(Auth::guard('school')->check())
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown" href="#">
                    <img src="{{ asset(auth('school')->user()->school_logo) }}"
                         alt="School Logo"
                         class="navbar-logo"
                         style="width:36px;height:36px;object-fit:contain;border-radius:50%;background:#fff;border:1px solid #E5E7EB;padding:2px;">
                </a>
                <div class="dropdown-menu dropdown-menu-right shadow-sm mt-2">
                    <div class="dropdown-item-text text-center">
                        <strong class="d-block">{{ ucwords(auth('school')->user()->school_name) }}</strong>
                        <small class="text-muted">Administrator</small>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('school.logout') }}" class="dropdown-item text-danger confirm-logout">
                        <i class="fas fa-sign-out-alt mr-2"></i> Logout
                    </a>
                </div>
            </li>
        @endif
    </ul>
</nav>
