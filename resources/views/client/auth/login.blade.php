@extends('layouts.client')

@section('title', 'Đăng nhập')

@section('content')
<div class="d-flex justify-content-center align-items-center py-5" style="min-height: 80vh;">
    <div class="auth-card p-4 shadow rounded bg-white" style="width: 100%; max-width: 420px;">
        <h3 class="text-center fw-bold text-primary mb-4">Đăng Nhập</h3>
        
        <form id="loginForm">
            <div class="mb-3">
                <label class="form-label fw-medium">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-medium">Mật khẩu</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="password" placeholder="Nhập mật khẩu" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>
            
            <div id="message" class="text-danger text-center mb-3 fw-bold"></div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">ĐĂNG NHẬP</button>
            
            <div class="text-center mt-3">
                Chưa có tài khoản?
                <a href="{{ route('register') }}" class="text-primary fw-medium"> Đăng ký ngay</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Ẩn hiện mật khẩu
    function togglePassword(inputId, btn) {
        const input = document.getElementById(inputId);
        const icon = btn.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }

    // Xử lý Đăng nhập & Lưu thông tin User
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const msg = document.getElementById('message');
        
        msg.innerText = 'Đang xử lý...';

        try {
            const res = await fetch("{{ url('/api/auth/login') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });

            const data = await res.json();
            
            if (res.ok) {
                localStorage.setItem('user_token', data.access_token);
                localStorage.setItem('user_info', JSON.stringify(data.user || { name: email.split('@')[0] }));
                
                window.location.href = "{{ route('home') }}";
            } else {
                msg.innerText = data.message || 'Đăng nhập thất bại';
            }
        } catch (err) {
            console.error(err);
            msg.innerText = 'Lỗi kết nối server';
        }
    });
</script>
@endpush