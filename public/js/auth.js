document.addEventListener('DOMContentLoaded', function () {

    // ----- XỬ LÝ ĐĂNG KÝ -----
    const registerForm = document.getElementById('registerForm');

    if (registerForm) {
        registerForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const formData = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };

            const messageDiv = document.getElementById('message');
            messageDiv.innerText = 'Đang xử lý...';
            messageDiv.style.color = 'blue';

            try {
                const response = await fetch(`${API_BASE_URL}/auth/register`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (response.ok) {
                    alert('Đăng ký thành công!');

                    // Lưu Token vào LocalStorage 
                    localStorage.setItem('access_token', result.data.access_token);
                    localStorage.setItem('user_info', JSON.stringify(result.data.user));

                    window.location.href = 'index.html';
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.innerText = result.message || 'Có lỗi xảy ra, vui lòng thử lại.';
                    console.error('Lỗi:', result);
                }

            } catch (error) {
                console.error('Lỗi mạng:', error);
                messageDiv.innerText = 'Không thể kết nối đến Server.';
            }
        });
    }


    // ----- XỬ LÝ ĐĂNG NHẬP -----
    const loginForm = document.getElementById('loginForm');

    if (loginForm) {
        loginForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const messageDiv = document.getElementById('message');

            messageDiv.innerText = 'Đang đăng nhập...';
            messageDiv.style.color = 'blue';

            try {
                const response = await fetch(`${API_BASE_URL}/auth/login`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email, password })
                });

                const result = await response.json();

                if (response.ok) {
                    // Lưu Token và thông tin User vào LocalStorage
                    localStorage.setItem('access_token', result.data.access_token);

                    localStorage.setItem('user_info', JSON.stringify(result.data.user));

                    localStorage.setItem('user_role', result.data.role);

                    alert('Đăng nhập thành công!');
                    window.location.href = 'index.html';
                } else {
                    messageDiv.style.color = 'red';
                    messageDiv.innerText = result.message || 'Email hoặc mật khẩu không đúng.';
                }
            } catch (error) {
                console.error('Lỗi:', error);
                messageDiv.innerText = 'Lỗi kết nối đến server.';
            }
        });
    }

});