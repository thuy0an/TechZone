<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-header">
            <h4>TechZone Admin</h4>
        </div>
        <div class="login-body">
            <form id="adminLoginForm">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" id="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Mật khẩu</label>
                    <input type="password" id="password" class="form-control" required>
                </div>
                <div id="message" class="text-danger text-center mb-3"></div>
                <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const msg = document.getElementById('message');
            
            msg.innerText = 'Đang xử lý...';

            try {
                // Gọi API Login (Route API cũ vẫn giữ nguyên)
                const res = await fetch("{{ url('/api/auth/admin/login') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });
                const data = await res.json();

                if (res.ok) {
                    localStorage.setItem('admin_token', data.data.access_token);
                    localStorage.setItem('admin_info', JSON.stringify(data.data.user));
                    window.location.href = "{{ route('admin.dashboard') }}";
                } else {
                    msg.innerText = data.message || 'Đăng nhập thất bại';
                }
            } catch (err) {
                msg.innerText = 'Lỗi kết nối';
            }
        });
    </script>
</body>
</html>