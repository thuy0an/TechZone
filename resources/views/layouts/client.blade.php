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

                <div id="nav-auth" class="d-flex gap-2">
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">Đăng nhập</a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">Đăng ký</a>
                </div>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2026 TechZone. Dự án môn học Web.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const API_BASE_URL = "{{ url('/api') }}"; // helper url() của Laravel

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