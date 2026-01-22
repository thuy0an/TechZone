document.addEventListener('DOMContentLoaded', function () {
    const adminForm = document.getElementById('adminLoginForm');

    if (adminForm) {
        adminForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            messageDiv.innerText = 'Đang xác thực...';
            messageDiv.style.color = 'blue';

            try {
                // Gọi API dành riêng cho Admin (Đã khai báo ở routes/api.php)
                // URL: http://techzone.test/api/auth/admin/login
                const response = await fetch(`${API_BASE_URL}/auth/admin/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();

                if (response.ok) {
                    localStorage.setItem('admin_token', result.data.access_token);
                    localStorage.setItem('admin_info', JSON.stringify(result.data.user));

                    alert('Chào mừng quản trị viên!');

                    window.location.href = 'admin/index.html';
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.innerText = result.message || 'Tài khoản hoặc mật khẩu không đúng';
                }

            } catch (error) {
                console.error('Lỗi:', error);
                messageDiv.innerText = 'Không thể kết nối Server';
            }
        });
    }
});