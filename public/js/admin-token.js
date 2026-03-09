/**
 * TechZone Admin – Token Storage
 *
 * S  – Single Responsibility: chỉ quản lý việc lưu/đọc token từ localStorage.
 *      Không biết gì về HTTP hay auth logic.
 *
 * D  – Dependency Inversion: các module HTTP/Auth phụ thuộc vào các hàm
 *      trừu tượng ở đây (getAdminToken) chứ không phụ thuộc vào storage cụ thể.
 *
 * Load order: admin-token.js → admin-api.js → admin-auth.js
 */

const ADMIN_API       = '/api/admin';
const ADMIN_TOKEN_KEY = 'admin_token';
const ADMIN_INFO_KEY  = 'admin_info';

function getAdminToken() {
    return localStorage.getItem(ADMIN_TOKEN_KEY);
}

function getAdminInfo() {
    const raw = localStorage.getItem(ADMIN_INFO_KEY);
    return raw ? JSON.parse(raw) : null;
}

function saveAdminSession(token, admin) {
    localStorage.setItem(ADMIN_TOKEN_KEY, token);
    localStorage.setItem(ADMIN_INFO_KEY, JSON.stringify(admin));
}

function clearAdminSession() {
    localStorage.removeItem(ADMIN_TOKEN_KEY);
    localStorage.removeItem(ADMIN_INFO_KEY);
}

function isAdminLoggedIn() {
    return !!getAdminToken();
}
