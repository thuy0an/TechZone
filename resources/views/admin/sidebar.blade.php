<div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark shadow" style="width: 260px; height: 100vh; position: sticky; top: 0; z-index: 1000;">
    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <i class="bi bi-shield-lock-fill fs-3 me-2 text-primary"></i>
        <span class="fs-4 fw-bold">TechZone Admin</span>
    </a>
    <hr class="border-secondary">
    
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : 'text-white' }} d-flex align-items-center mb-2">
                <i class="bi bi-speedometer2 me-3 fs-5"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : 'text-white' }} d-flex align-items-center mb-2">
                <i class="bi bi-box-seam me-3 fs-5"></i> Sản phẩm
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white d-flex align-items-center mb-2">
                <i class="bi bi-receipt me-3 fs-5"></i> Đơn hàng
            </a>
        </li>
        <li>
            <a href="#" class="nav-link text-white d-flex align-items-center mb-2">
                <i class="bi bi-people me-3 fs-5"></i> Khách hàng
            </a>
        </li>
    </ul>
    
    <hr class="border-secondary">
    
    <div class="dropdown p-3 border-top border-secondary">
        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
            <img src="https://ui-avatars.com/api/?name=Admin&background=0D8ABC&color=fff" id="admin-avatar-sidebar" width="32" height="32" class="rounded-circle me-2">
            <strong id="admin-name-sidebar">Admin</strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
            <li><a class="dropdown-item" href="#">Cài đặt</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="#" onclick="handleLogout()">Đăng xuất</a></li>
        </ul>
    </div>
</div>