<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title') - TechZone Admin</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    <style>
        body { background-color: #f4f6f9; }
        .main-content { min-height: 100vh; width: 100%; }
    </style>
    @stack('css')
</head>
<body class="d-flex">

    @include('admin.sidebar')

    <div class="main-content w-100">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const API_BASE_URL = "{{ url('/api') }}";
        
        // Setup Fetch CSRF
        const originalFetch = window.fetch;
        window.fetch = function() {
            let args = arguments;
            let url = args[0];
            let options = args[1] || {};
            options.headers = options.headers || {};
            const token = document.querySelector('meta[name="csrf-token"]').content;
            options.headers['X-CSRF-TOKEN'] = token;
            
            // Tự động thêm Bearer Token nếu có trong localStorage
            const adminToken = localStorage.getItem('admin_token');
            if(adminToken) {
                options.headers['Authorization'] = `Bearer ${adminToken}`;
            }

            args[1] = options;
            return originalFetch.apply(this, args);
        };

        // Logic hiển thị tên Admin từ LocalStorage
        document.addEventListener('DOMContentLoaded', () => {
            const adminInfo = localStorage.getItem('admin_info');
            if(adminInfo) {
                try {
                    const user = JSON.parse(adminInfo);
                    document.getElementById('admin-name-sidebar').innerText = user.name;
                    document.getElementById('admin-avatar-sidebar').src = `https://ui-avatars.com/api/?name=${user.name}&background=0D8ABC&color=fff`;
                } catch(e) {}
            } else {
                // Nếu chưa đăng nhập mà cố vào trang admin -> đá về login
                // (Trừ khi đang ở trang login - logic này sẽ xử lý sau)
            }
        });

        function handleLogout() {
            if(confirm('Đăng xuất quản trị viên?')) {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_info');
                window.location.href = "{{ route('admin.login') }}";
            }
        }
    </script>
    
    @stack('scripts')
</body>
</html>