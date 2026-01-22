/**
 * TechZone - Main Application
 * Logic chính của frontend
 */

// ============================================
// DOM Ready
// ============================================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('TechZone loaded!!!');
    // check Login status 
    checkLoginStatus();

    // Update cart count on page load
    updateCartCount();

    // Test API connection
    await checkAPIStatus();

    // Load featured products (if on home page)
    await loadFeaturedProducts();
});

// ============================================
// API Status Check
// ============================================
async function checkAPIStatus() {
    const statusElement = document.getElementById('api-status');
    if (!statusElement) return;

    try {
        const data = await testAPI();
        statusElement.innerHTML = `
            <p style="color: #10b981;">✅ ${data.message}</p>
            <p style="font-size: 0.9rem; opacity: 0.8;">Thời gian: ${data.timestamp}</p>
        `;
        statusElement.classList.add('success');
    } catch (error) {
        statusElement.innerHTML = `
            <p style="color: #ef4444;">❌ Không thể kết nối API</p>
            <p style="font-size: 0.9rem; opacity: 0.8;">Lỗi: ${error.message}</p>
        `;
        statusElement.classList.add('error');
    }
}

// ============================================
// Load Products
// ============================================
async function loadFeaturedProducts() {
    const container = document.getElementById('featured-products');
    if (!container) return;

    try {
        // Tạm thời hiển thị sản phẩm mẫu vì chưa có API products
        const sampleProducts = [
            {
                id: 1,
                name: 'iPhone 15 Pro Max',
                price: 34990000,
                image: 'https://via.placeholder.com/300x200?text=iPhone+15'
            },
            {
                id: 2,
                name: 'MacBook Pro M3',
                price: 52990000,
                image: 'https://via.placeholder.com/300x200?text=MacBook+Pro'
            },
            {
                id: 3,
                name: 'Samsung Galaxy S24 Ultra',
                price: 31990000,
                image: 'https://via.placeholder.com/300x200?text=Galaxy+S24'
            },
            {
                id: 4,
                name: 'AirPods Pro 2',
                price: 6990000,
                image: 'https://via.placeholder.com/300x200?text=AirPods+Pro'
            }
        ];

        renderProducts(container, sampleProducts);

        // Uncomment khi có API thực:
        // const products = await getProducts();
        // renderProducts(container, products);

    } catch (error) {
        container.innerHTML = `
            <div class="loading">
                <p>⚠️ Không thể tải sản phẩm</p>
                <p style="font-size: 0.9rem;">${error.message}</p>
            </div>
        `;
    }
}

/**
 * Render danh sách sản phẩm ra HTML
 * @param {HTMLElement} container - Container element
 * @param {array} products - Danh sách sản phẩm
 */
function renderProducts(container, products) {
    if (!products || products.length === 0) {
        container.innerHTML = '<div class="loading">Không có sản phẩm nào</div>';
        return;
    }

    container.innerHTML = products.map(product => `
        <div class="product-card">
            <img src="${product.image}" alt="${product.name}">
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="product-price">${formatPrice(product.price)}</p>
                <button class="btn btn-secondary" onclick="handleAddToCart(${product.id}, '${product.name}', ${product.price})">
                    🛒 Thêm vào giỏ
                </button>
            </div>
        </div>
    `).join('');
}

// ============================================
// Cart Handlers
// ============================================
function handleAddToCart(id, name, price) {
    addToCart({ id, name, price });
    showNotification(`Đã thêm "${name}" vào giỏ hàng!`);
}

// ============================================
// Utilities
// ============================================

/**
 * Format giá tiền VND
 * @param {number} price - Giá tiền
 * @returns {string} Giá đã format
 */
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(price);
}

/**
 * Hiển thị thông báo
 * @param {string} message - Nội dung thông báo
 * @param {string} type - Loại: 'success' | 'error'
 */
function showNotification(message, type = 'success') {
    // Tạo notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        z-index: 1000;
        animation: slideIn 0.3s ease;
    `;
    notification.textContent = message;

    document.body.appendChild(notification);

    // Tự động ẩn sau 3 giây
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

function checkLoginStatus() {
    const authAction = document.getElementById("auth-action");

    const userInfo = JSON.parse(localStorage.getItem('user_info'));
    const token = localStorage.getItem('access_token');

    if (userInfo && token && authAction) {
        authAction.innerHTML = `
        <span class="nav-link" style="font-weight: bold; color: #333; border: 1px solid #ddd; border-radius: 10px; padding: 10px">
                Xin chào, ${userInfo.name}
            </span>
            <a href="#" onclick="handleLogout()" class="nav-link btn-login" style="color: #dc3545;">
                Đăng xuất
            </a>
        `;
    }
}

async function handleLogout() {
    if (confirm("Bạn có chắc muốn đăng xuất ?")) {
        try {
            const token = localStorage.getItem('access_token');

            await fetch(`${API_BASE_URL}/auth/logout`, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
        }
        catch (error) {
            console.log('Lỗi logout: ', error);
        } finally {
            localStorage.removeItem('user_info');
            localStorage.removeItem('user_role');
            localStorage.removeItem('access_token');

            alert('Đăng xuất thành công !');
            window.location.href = 'login.html';
        }
    }
}

// CSS Animation (thêm vào head)
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(100%); opacity: 0; }
    }
`;
document.head.appendChild(style);
