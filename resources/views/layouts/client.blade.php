<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'TechZone - Cửa hàng Công nghệ')</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    @stack('css')
</head>
<body>

    <header class="header bg-white shadow-sm sticky-top py-3">
        <div class="container d-flex justify-content-between align-items-center">
            <a href="{{ route('home') }}" class="text-decoration-none d-flex align-items-center gap-2">
                <i class="bi bi-cpu-fill fs-3 text-primary"></i>
                <span class="fs-4 fw-bold text-dark">TechZone</span>
            </a>
            
            <nav class="d-flex gap-5 align-items-center">
                <a href="{{ route('home') }}" class="nav-link text-dark fw-medium">Trang chủ</a>
                
                <button class="btn btn-outline-dark position-relative border-0" type="button" onclick="Cart.open()">
                    <i class="bi bi-cart-fill fs-5"></i>
                    <span id="cart-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger px-3 py-2" style="display: none;">
                        0
                    </span>
                </button>

                <div id="nav-auth" class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-md">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-md">Đăng ký</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- OFF CANVAS GIỎ HÀNG  --}}
    <div class="offcanvas offcanvas-end" tabindex="-1" id="cartSidebar" aria-labelledby="cartSidebarLabel">
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title fw-bold" id="cartSidebarLabel">
                <i class="bi bi-bag-check-fill text-primary me-2"></i>Giỏ hàng của bạn
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body p-0" id="cart-body">
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>

        <div class="offcanvas-footer border-top p-3 bg-light" id="cart-footer" style="display: none;">
            <div class="d-flex justify-content-between mb-3">
                <span class="fw-bold">Tổng tạm tính:</span>
                <span class="fw-bold text-danger fs-5" id="cart-total">0 đ</span>
            </div>
            <a href="#" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                TIẾN HÀNH THANH TOÁN
            </a>
        </div>
    </div>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2026 TechZone. Dự án môn học Web.</p>
        </div>
    </footer>

    {{-- TOAST THÔNG BÁO --}}
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
        <div id="liveToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body fw-bold" id="toast-message">
                    </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/client/cart.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Kiểm tra Token và Info trong LocalStorage
        const token = localStorage.getItem('user_token');
        const userInfoStr = localStorage.getItem('user_info');
        const navAuth = document.getElementById('nav-auth');

            if (token && userInfoStr && navAuth) {
                try {
                    const user = JSON.parse(userInfoStr);
                    
                    // Thay thế HTML của vùng nav-auth bằng thông tin user
                    navAuth.innerHTML = `
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                    ${user.name.charAt(0).toUpperCase()}
                                </div>
                                <span class="fw-medium">${user.name}</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="#">Hồ sơ cá nhân</a></li>
                                <li><a class="dropdown-item" href="#">Đơn hàng của tôi</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="logoutClient(event)">Đăng xuất</a></li>
                            </ul>
                        </div>
                    `;
                } catch (e) {
                    console.error("Lỗi parse user info", e);
                    localStorage.removeItem('user_info'); // Xóa nếu lỗi
                }
            }
        });

        // Hàm đăng xuất
        function logoutClient(e) {
            e.preventDefault();
            if(confirm('Bạn có chắc muốn đăng xuất?')) {
                localStorage.removeItem('user_token');
                localStorage.removeItem('user_info');
                window.location.href = "{{ route('home') }}"; // Reload lại trang để về trạng thái chưa đăng nhập
            }
        }
        
        // Setup gửi CSRF Token
        const originalFetch = window.fetch;
        window.fetch = function() {
            let args = arguments;
            let url = args[0];
            let options = args[1] || {};
            options.headers = options.headers || {};
            
            const token = document.querySelector('meta[name="csrf-token"]').content;
            options.headers['X-CSRF-TOKEN'] = token;
            options.headers['Accept'] = 'application/json';

            args[1] = options;
            return originalFetch.apply(this, args);
        };
    </script>
    @stack('scripts')
</body>
</html>