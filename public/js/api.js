/**
 * TechZone - API Module
 * Các hàm gọi API Laravel
 */

// Base URL của API
const API_BASE_URL = '/api';
const CART_KEY = 'techzone_cart'

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
        'X-Session-ID': getSessionId() // <---Luôn gửi Session ID
    };

    const defaultOptions = {
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    };

    const token = localStorage.getItem('auth_token');
    if (token) {
        headers['Authorization'] = `Bearer ${token}`;
    }

    // Merge options
    const config = {
        ...options,
        headers: {
            ...headers,
            ...(options.headers || {}) // Cho phép ghi đè header
        },
        cache: 'no-store'
    };

    try {
        const response = await fetch(url, config);
        const result = await response.json();

        // Xử lý lỗi từ Server trả về (4xx, 5xx)
        if (!response.ok) {
            throw new Error(result.message || `Lỗi ${response.status}: ${response.statusText}`);
        }

        return result;
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}


// ============================================
// HELPERS
// ============================================

/**
 * Lấy hoặc tạo Session ID cho khách vãng lai
 * (Dùng để định danh giỏ hàng khi chưa đăng nhập)
 */
function getSessionId() {
    let sessionId = localStorage.getItem('techzone_session_id');
    if (!sessionId) {
        // Tự sinh UUID v4 đơn giản
        sessionId = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
        localStorage.setItem('techzone_session_id', sessionId);
    }
    return sessionId;
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
// Cart API (Database Server-side)
// ============================================


/**
 * Lấy giỏ hàng hiện tại từ Local Storage
 */

function getLocalCart() {
    const cart = localStorage.getItem(CART_KEY);
    return cart ? JSON.parse(cart) : { items: [], total_price: 0 };
}

/**
 * Lưu giỏ hàng hiện tại vào localStorage và update UI
 */
function saveLocalCart(cartData) {
    localStorage.setItem(CART_KEY, JSON.stringify(cartData));
    updateBadgeUI(cartData);
}

/**
 * Lấy giỏ hàng hiện tại từ Server lưu về Local
 */
async function syncCartFromServer() {
    try {
        const res = await apiRequest('/cart?t=' + new Date().getTime());
        if (res && res.data) {
            saveLocalCart(res.data);
            return res.data;
        }
    } catch (e) {
        console.warn('Không thể đồng bộ giỏ hàng:', e);
    }
    return null;
}

/**
 * Thêm sản phẩm vào giỏ - lưu vào DB
 * @param {number} productId 
 * @param {number} quantity 
 */
async function addToCart(productId, quantity = 1) {
    return await apiRequest('/cart/add', {
        method: 'POST',
        body: JSON.stringify({
            product_id: productId,
            quantity: parseInt(quantity)
        })
    });
}

/**
 * Cập nhật số lượng item - lưu vào DB
 * @param {number} itemId - ID của item trong giỏ (không phải ID sản phẩm)
 * @param {number} quantity - Số lượng mới
 */
async function updateCartItem(itemId, quantity) {
    return await apiRequest(`/cart/update/${itemId}`, {
        method: 'PUT',
        body: JSON.stringify({ quantity: parseInt(quantity) })
    });
}

/**
 * Xóa sản phẩm khỏi giỏ - lưu vào DB
 */
async function removeCartItem(itemId) {
    return await apiRequest(`/cart/remove/${itemId}`, {
        method: 'DELETE'
    });
}

/**
 * Xóa toàn bộ giỏ hàng - lưu vào DB
 */
async function clearCart() {
    return await apiRequest('/cart/clear', {
        method: 'DELETE'
    });
}


// ============================================
// UI Helper 
// ============================================

/**
 * Cập nhật Badge Giỏ hàng
 * @param {object|null} cart - Dữ liệu giỏ hàng mới nhất (nếu có)
 */
function updateBadgeUI(cart = null) {
    if (!cart) cart = getLocalCart();

    const count = (cart.items)
        ? cart.items.reduce((sum, item) => sum + parseInt(item.quantity), 0)
        : 0;

    const badge = document.getElementById('cart-badge');
    if (badge) {
        badge.innerText = count;
        badge.style.display = count > 0 ? 'inline-block' : 'none';

        // Hiệu ứng
        if (count > 0) {
            badge.classList.add('bg-warning');
            setTimeout(() => badge.classList.remove('bg-warning'), 300);
        }
    }
}

/**
 * Cập nhật Badge Giỏ hàng
 * @param {number} amount - Số tiền
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

document.addEventListener('DOMContentLoaded', () => {
    updateBadgeUI();
    syncCartFromServer();
});