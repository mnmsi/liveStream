<!-- Sidebar Start -->
<div class="sidebar pe-4 pb-3">
    <nav class="navbar bg-secondary navbar-dark">
        <a href="{{route('dashboard')}}" class="navbar-brand mx-4 mb-3">
            <h3 class="text-primary"><i class="fa fa-user-edit me-2"></i>Live Stream</h3>
        </a>
        <div class="navbar-nav w-100">
            <a href="{{route('dashboard')}}" class="nav-item nav-link {{request()->is('dashboard') ? 'active' : ''}}">
                <i class="fa fa-tachometer-alt me-2"></i>Dashboard
            </a>

            <a href="{{route('config.list')}}" class="nav-item nav-link {{request()->is('config*') ? 'active' : ''}}">
                <i class="fa fa-th me-2"></i>Configs
            </a>
        </div>
    </nav>
</div>
<!-- Sidebar End -->
