@extends('layouts.client')

@section('title', 'Đăng ký tài khoản')

@section('content')
<div class="d-flex justify-content-center align-items-center py-5" style="min-height: 80vh; background: #f8f9fa;">
    <div class="auth-card p-4 shadow rounded bg-white" style="width: 100%; max-width: 500px;">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-success"><i class="bi bi-person-plus-fill me-2"></i>Đăng Ký</h3>
            <p class="text-muted small">Trở thành thành viên TechZone ngay hôm nay</p>
        </div>
        
        <form id="registerForm">
            <div class="input-group mb-3">
                <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                <input type="text" class="form-control" id="name" placeholder="Họ và tên của bạn" required>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                        <input type="email" class="form-control" id="email" placeholder="Email" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-phone"></i></span>
                        <input type="text" class="form-control" id="phone" placeholder="Số điện thoại">
                    </div>
                </div>
            </div>

            <div class="input-group mb-3">
                <input type="password" class="form-control" id="password" required placeholder="Mật khẩu">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <div class="input-group mb-4">
                <input type="password" class="form-control" id="password_confirmation" required placeholder="Nhập lại mật khẩu">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirmation', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>

            <div id="message" class="text-center fw-bold mb-3 small" style="min-height: 20px;"></div>

            <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-success fw-bold py-2">
                    HOÀN TẤT ĐĂNG KÝ
                </button>
            </div>

            <div class="text-center">
                <span class="text-muted small">Đã có tài khoản? </span>
                <a href="{{ route('login') }}" class="text-decoration-none fw-bold text-success">Đăng nhập ngay</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('registerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const btn = this.querySelector('button[type="submit"]');
        const msg = document.getElementById('message');
        
        const formData = {
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            phone: document.getElementById('phone').value,
            password: document.getElementById('password').value,
            password_confirmation: document.getElementById('password_confirmation').value
        };

        if(formData.password !== formData.password_confirmation) {
            msg.className = 'text-center text-danger fw-bold mb-3 small';
            msg.innerText = 'Mật khẩu nhập lại không khớp!';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...';
        msg.innerText = '';

        try {
            const res = await fetch("{{ url('/api/auth/register') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await res.json();

            if (res.ok) {
                msg.className = 'text-center text-success fw-bold mb-3 small';
                msg.innerText = 'Đăng ký thành công! Đang chuyển hướng...';
                
                setTimeout(() => {
                    window.location.href = "{{ route('login') }}";
                }, 1500);
            } else {
                msg.className = 'text-center text-danger fw-bold mb-3 small';
                msg.innerText = data.message || 'Đăng ký thất bại. Vui lòng kiểm tra lại thông tin.';
            }
        } catch (err) {
            console.error(err);
            msg.className = 'text-center text-danger fw-bold mb-3 small';
            msg.innerText = 'Lỗi kết nối đến máy chủ.';
        } finally {
            btn.disabled = false;
            if(btn.innerText !== 'HOÀN TẤT ĐĂNG KÝ') btn.innerText = 'HOÀN TẤT ĐĂNG KÝ';
        }
    });
</script>
@endpush