/**
 * TechZone Storefront - Auth Module
 * Xử lý phiên đăng nhập của khách hàng
 */

const TOKEN_KEY = 'storefront_token';
const USER_KEY = 'storefront_user';

// Lấy Token hiện tại
function getAuthToken() {
    return localStorage.getItem(TOKEN_KEY);
}

// Lấy thông tin User hiện tại
function getCurrentUser() {
    const userStr = localStorage.getItem(USER_KEY);
    return userStr ? JSON.parse(userStr) : null;
}

// Kiểm tra trạng thái đăng nhập
function isLoggedIn() {
    return !!getAuthToken();
}

// Lưu phiên đăng nhập (Gọi sau khi API login thành công)
function setSession(token, user) {
    localStorage.setItem(TOKEN_KEY, token);
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

// Xóa phiên đăng nhập
function clearSession() {
    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
}

/**
 * Hàm Đăng ký tài khoản mới
 * @param {object} userData - Bao gồm name, email, password, password_confirmation, phone_number
 */
async function registerCustomer(userData) {
    try {
        const response = await apiRequest('/storefront/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });

        // Tương tự login, lưu token và user vào localStorage ngay sau khi đăng ký thành công
        if (response.data && response.data.access_token) {
            setSession(response.data.access_token, response.data.user);
        }

        return response;
    } catch (error) {
        throw error;
    }
}


/**
 * Hàm đăng nhập
 * @param {String} email
 * @param {String} password
 */
async function loginCustomer(email, password) {
    try {
        // Gọi API login đã định nghĩa trong route
        const response = await apiRequest('/storefront/login', {
            method: 'POST',
            body: JSON.stringify({ email, password })
        });

        // Lưu token và user vào localStorage
        if (response.data && response.data.access_token) {
            setSession(response.data.access_token, response.data.user);
        }
        return response;
    } catch (error) {
        throw error;
    }
}

// Hàm Đăng xuất
async function logoutCustomer() {
    try {
        if (isLoggedIn()) {
            await apiRequest('/storefront/logout', { method: 'POST' });
        }
    } catch (e) {
        console.error('Lỗi khi gọi API logout', e);
    } finally {
        clearSession();
        window.location.href = '/login.html';
    }
}

async function checkAddressAndRedirect(redirectTo = '/') {
    try {
        // Gọi API lấy danh sách địa chỉ của user hiện tại
        const response = await apiRequest('/storefront/addresses', { method: 'GET' });

        // Nếu mảng data rỗng (chưa có địa chỉ nào)
        if (response.data && response.data.length === 0) {
            window.location.href = '/setup-address.html';
        } else {
            window.location.href = redirectTo || '/';
        }
    } catch (error) {
        console.error('Lỗi khi kiểm tra địa chỉ:', error);
        window.location.href = redirectTo || '/';
    }
}