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
    const url = endpoint.startsWith('http') ? endpoint : `${API_BASE_URL}${endpoint}`;

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

/**
 * Lấy danh sách danh mục cho storefront
 * @returns {Promise<object>}
 */
async function getStorefrontCategories() {
    return await apiRequest('/storefront/categories');
}

/**
 * Lấy danh sách thương hiệu cho storefront
 * @returns {Promise<object>}
 */
async function getStorefrontBrands() {
    return await apiRequest('/storefront/brands');
}

/**
 * Lấy sản phẩm theo danh mục có phân trang
 * @param {number} categoryId - ID danh mục
 * @param {number} page - Trang hiện tại (mặc định: 1)
 * @param {number} limit - Số SP mỗi trang (mặc định: 10)
 * @returns {Promise<object>}
 */
async function getProductsByCategory(categoryId, page = 1, limit = 10) {
    return await apiRequest(`/storefront/products/category/${categoryId}?page=${page}&limit=${limit}`);
}

/**
 * Lấy chi tiết sản phẩm cho storefront
 * @param {number} id - Product ID
 * @returns {Promise<object>}
 */
async function getStorefrontProductDetail(id) {
    return await apiRequest(`/storefront/products/${id}`);
}

/**
 * Tìm kiếm sản phẩm cơ bản (theo tên)
 * @param {string} keyword
 * @param {number} page
 * @param {number} perPage
 * @returns {Promise<object>}
 */
async function searchStorefrontProductsBasic(keyword, page = 1, perPage = 12) {
    const params = new URLSearchParams({
        keyword,
        page: String(page),
        per_page: String(perPage),
    });
    return await apiRequest(`/storefront/products/search/basic?${params.toString()}`);
}

/**
 * Tìm kiếm sản phẩm nâng cao
 * @param {object} filters
 * @param {number} page
 * @param {number} perPage
 * @returns {Promise<object>}
 */
async function searchStorefrontProductsAdvanced(filters = {}, page = 1, perPage = 12) {
    const params = new URLSearchParams({
        page: String(page),
        per_page: String(perPage),
    });

    if (filters.keyword) params.set('keyword', filters.keyword);
    if (filters.category_id) params.set('category_id', String(filters.category_id));
    if (filters.brand_id) params.set('brand_id', String(filters.brand_id));
    if (filters.min_price !== '' && filters.min_price !== null && filters.min_price !== undefined) {
        params.set('min_price', String(filters.min_price));
    }
    if (filters.max_price !== '' && filters.max_price !== null && filters.max_price !== undefined) {
        params.set('max_price', String(filters.max_price));
    }

    return await apiRequest(`/storefront/products/search/advanced?${params.toString()}`);
}

// ============================================
// Cart API (Server based)
// ============================================

/**
 * Lấy giỏ hàng từ API
 * @returns {Promise<object>}
 */
async function getCart() {
    return await apiRequest('/client/cart');
}

/**
 * Cập nhật giỏ hàng theo số lượng cộng/trừ
 * @param {number} productId
 * @param {number} quantityDelta
 */
async function updateCartItem(productId, quantityDelta) {
    return await apiRequest('/client/cart/update', {
        method: 'POST',
        body: JSON.stringify({
            product_id: productId,
            quantity: quantityDelta,
        })
    });
}

/**
 * Thêm sản phẩm vào giỏ hàng
 * @param {object} product - Sản phẩm cần thêm
 * @param {number} quantity - Số lượng
 */
async function addToCart(product, quantity = 1) {
    const response = await updateCartItem(product.id, quantity);
    await updateCartCount();
    return response;
}

/**
 * Xóa sản phẩm khỏi giỏ hàng
 * @param {number} productId - ID sản phẩm
 */
async function removeFromCart(productId) {
    const response = await updateCartItem(productId, 0);
    await updateCartCount();
    return response;
}

/**
 * Áp dụng mã khuyến mãi cho giỏ hàng
 * @param {string} promotionCode
 */
async function applyPromotion(promotionCode) {
    return await apiRequest('/storefront/checkout/apply-promotion', {
        method: 'POST',
        body: JSON.stringify({ promotion_code: promotionCode })
    });
}

/**
 * Gửi yêu cầu checkout
 * @param {object} payload
 */
async function checkoutOrder(payload) {
    return await apiRequest('/client/orders', {
        method: 'POST',
        body: JSON.stringify(payload)
    });
}

/**
 * Lấy tóm tắt đơn hàng
 * @param {number} orderId
 */
async function getOrderSummary(orderId) {
    return await apiRequest(`/client/orders/${orderId}/summary`);
}

/**
 * Cập nhật số lượng trong badge giỏ hàng
 */
async function updateCartCount() {
    try {
        if (typeof isLoggedIn === 'function' && !isLoggedIn()) {
            const countElement = document.getElementById('cart-count');
            if (countElement) {
                countElement.textContent = '0';
            }
            return;
        }

        const response = await getCart();
        const items = response.data?.items || [];
        const count = items.reduce((total, item) => total + Number(item.quantity || 0), 0);
        const countElement = document.getElementById('cart-count');
        if (countElement) {
            countElement.textContent = count;
        }
    } catch (error) {
        const countElement = document.getElementById('cart-count');
        if (countElement) {
            countElement.textContent = '0';
        }
    }
}
