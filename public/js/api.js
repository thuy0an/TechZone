/**
 * TechZone - API Module
 * Các hàm gọi API Laravel
 */

// Base URL của API
const API_BASE_URL = '/api';

/**
 * Hàm fetch wrapper với xử lý lỗi
 * @param {string} endpoint - API endpoint (không cần /api prefix)
 * @param {object} options - Fetch options
 * @returns {Promise<any>} Response data
 */
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE_URL}${endpoint}`;

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(options.headers || {})
    };

    // Tự động đính kèm Token của Customer nếu có
    const token = localStorage.getItem('storefront_token');
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const config = { ...options, headers };

    try {
        const response = await fetch(url, config);
        const data = await response.json();

        if (!response.ok) {
            // Nếu token hết hạn hoặc lỗi 401, tự động xóa session và bắt đăng nhập lại
            if (response.status === 401 && endpoint !== '/storefront/login') {
                localStorage.removeItem('storefront_token');
                localStorage.removeItem('storefront_user');
                window.location.href = '/login.html';
            }

            const err = new Error(data.message || 'Lỗi kết nối API');
            err.status = response.status;
            err.data = data;
            throw err;
        }

        return data;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

// ============================================
// Test API
// ============================================

/**
 * Kiểm tra kết nối API
 * @returns {Promise<object>}
 */
async function testAPI() {
    return await apiRequest('/test');
}

// ============================================
// Products API
// ============================================

/**
 * Lấy danh sách tất cả sản phẩm
 * @returns {Promise<array>}
 */
async function getProducts() {
    return await apiRequest('/products');
}

/**
 * Lấy thông tin 1 sản phẩm theo ID
 * @param {number} id - Product ID
 * @returns {Promise<object>}
 */
async function getProduct(id) {
    return await apiRequest(`/products/${id}`);
}

/**
 * Tạo sản phẩm mới
 * @param {object} productData - Dữ liệu sản phẩm
 * @returns {Promise<object>}
 */
async function createProduct(productData) {
    return await apiRequest('/products', {
        method: 'POST',
        body: JSON.stringify(productData),
    });
}

/**
 * Cập nhật sản phẩm
 * @param {number} id - Product ID
 * @param {object} productData - Dữ liệu cập nhật
 * @returns {Promise<object>}
 */
async function updateProduct(id, productData) {
    return await apiRequest(`/products/${id}`, {
        method: 'PUT',
        body: JSON.stringify(productData),
    });
}

/**
 * Xóa sản phẩm
 * @param {number} id - Product ID
 * @returns {Promise<object>}
 */
async function deleteProduct(id) {
    return await apiRequest(`/products/${id}`, {
        method: 'DELETE',
    });
}

// ============================================
// Categories API
// ============================================

async function getCategories() {
    return await apiRequest('/categories');
}

// ============================================
// Cart API (Local Storage based)
// ============================================

/**
 * Lấy giỏ hàng từ localStorage
 * @returns {array}
 */
function getCart() {
    const cart = localStorage.getItem('cart');
    return cart ? JSON.parse(cart) : [];
}

/**
 * Thêm sản phẩm vào giỏ hàng
 * @param {object} product - Sản phẩm cần thêm
 * @param {number} quantity - Số lượng
 */
function addToCart(product, quantity = 1) {
    const cart = getCart();
    const existingItem = cart.find(item => item.id === product.id);

    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.push({ ...product, quantity });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 * @param {number} productId - ID sản phẩm
 */
function removeFromCart(productId) {
    const cart = getCart().filter(item => item.id !== productId);
    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
}

/**
 * Cập nhật số lượng trong badge giỏ hàng
 */
function updateCartCount() {
    const cart = getCart();
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    const countElement = document.getElementById('cart-count');
    if (countElement) {
        countElement.textContent = count;
    }
}
