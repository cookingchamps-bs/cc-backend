<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a class="nav-link" href="javascript:;" role="button">
                Welcome
                @if(Auth::guard('admin')->check())
                    <b>{{ Auth::guard('admin')->user()->name }}</b>
                @elseif(Auth::guard('vendor')->check())
                    <b>{{ Auth::guard('vendor')->user()->username }}</b>
                @endif
            </a>
        </li>
    </ul>
    <ul class="navbar-nav ml-auto">
        <li class="nav-item d-none d-md-inline-block">
            <span class="nav-link text-muted" style="font-weight:600;color:#48335C !important;">Cooking Champs</span>
        </li>
    </ul>
</nav>
