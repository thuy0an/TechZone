/**
 * TechZone - Main Application
 * Logic chính của frontend
 */

// State quản lý danh mục & phân trang
let currentCategoryId = null;
let currentPage = 1;
const PRODUCTS_PER_PAGE = 8;

// Emoji icons cho từng loại danh mục
const CATEGORY_ICONS = {
    'điện thoại': '📱', 'phone': '📱',
    'laptop': '💻', 'máy tính': '💻', 'pc': '🖥️',
    'tablet': '📋', 'máy tính bảng': '📋',
    'tai nghe': '🎧', 'headphone': '🎧', 'earphone': '🎧',
    'đồng hồ': '⌚', 'watch': '⌚', 'smartwatch': '⌚',
    'phụ kiện': '🔌', 'accessory': '🔌', 'accessories': '🔌',
    'loa': '🔊', 'speaker': '🔊',
    'camera': '📷', 'màn hình': '🖥️', 'monitor': '🖥️',
    'bàn phím': '⌨️', 'keyboard': '⌨️',
    'chuột': '🖱️', 'mouse': '🖱️',
    'tivi': '📺', 'tv': '📺',
    'máy in': '🖨️', 'printer': '🖨️',
    'ổ cứng': '💾', 'storage': '💾', 'ssd': '💾',
    'ram': '🧩', 'linh kiện': '🧩',
    'gaming': '🎮', 'game': '🎮',
    'mạng': '📡', 'network': '📡', 'router': '📡',
};

function getCategoryIcon(name) {
    const lower = name.toLowerCase();
    for (const [key, icon] of Object.entries(CATEGORY_ICONS)) {
        if (lower.includes(key)) return icon;
    }
    return '📦';
}

// ============================================
// DOM Ready
// ============================================
document.addEventListener('DOMContentLoaded', async () => {
    console.log('TechZone loaded!');
    updateCartCount();

    // Trang chủ: load featured products
    if (document.getElementById('featured-products')) {
        await loadFeaturedProducts();
    }

    // Trang products: load categories + products
    if (document.getElementById('categories-list')) {
        await loadCategories();
    }
});

// ============================================
// Homepage: Featured Products (có phân trang)
// ============================================
async function loadFeaturedProducts() {
    const container = document.getElementById('featured-products');
    const paginationContainer = document.getElementById('pagination-container');
    if (!container) return;

    container.innerHTML = '<div class="loading">Đang tải sản phẩm...</div>';

    try {
        const response = await apiRequest(`/storefront/products?per_page=${PRODUCTS_PER_PAGE}&page=1`);
        const products = response.data || [];
        const pagination = response.pagination || null;

        if (!products || products.length === 0) {
            container.innerHTML = '<div class="loading">Chưa có sản phẩm nào</div>';
            return;
        }

        renderProducts(container, products);

        // Hiển thị pagination ở góc dưới phải
        if (pagination && pagination.last_page > 1 && paginationContainer) {
            currentCategoryId = '__all_home__';
            renderPagination(paginationContainer, pagination);
            paginationContainer.style.display = 'flex';
        }
    } catch (error) {
        container.innerHTML = `
            <div class="loading">
                <p>⚠️ Không thể tải sản phẩm</p>
                <p style="font-size: 0.9rem;">${error.message}</p>
            </div>
        `;
    }
}

// ============================================
// Products Page: Load Categories
// ============================================
async function loadCategories() {
    const container = document.getElementById('categories-list');
    if (!container) return;

    try {
        const response = await getStorefrontCategories();
        const categories = response.data || response;

        if (!categories || categories.length === 0) {
            container.innerHTML = '<div class="loading">Chưa có danh mục nào</div>';
            return;
        }

        let html = `
            <div class="category-card active" id="category-card-all" onclick="selectAllProducts()">
                <div class="category-icon-wrapper">
                    <span class="category-icon">🛍️</span>
                </div>
                <span class="category-name">Tất cả</span>
                <span class="category-arrow">→</span>
            </div>
        `;

        html += categories.map(cat => {
            const icon = getCategoryIcon(cat.name);
            return `
            <div class="category-card" id="category-card-${cat.id}" onclick="selectCategory(${cat.id}, '${cat.name.replace(/'/g, "\\'")}')">
                <div class="category-icon-wrapper">
                    <span class="category-icon">${icon}</span>
                </div>
                <span class="category-name">${cat.name}</span>
                <span class="category-arrow">→</span>
            </div>
            `;
        }).join('');

        container.innerHTML = html;
        await loadAllProducts();
    } catch (error) {
        container.innerHTML = `
            <div class="loading">
                <p>⚠️ Không thể tải danh mục</p>
                <p style="font-size: 0.9rem;">${error.message}</p>
            </div>
        `;
    }
}

// ============================================
// Products Page: Load All Products (có phân trang)
// ============================================
async function loadAllProducts(page = 1) {
    currentCategoryId = '__all__';
    currentPage = page;

    highlightCategory('all');

    const title = document.getElementById('category-products-title');
    if (title) title.textContent = 'Tất cả sản phẩm';

    const container = document.getElementById('category-products');
    const paginationContainer = document.getElementById('pagination-container');
    if (!container) return;

    container.innerHTML = '<div class="loading">Đang tải sản phẩm...</div>';
    if (paginationContainer) paginationContainer.style.display = 'none';

    try {
        const response = await apiRequest(`/storefront/products?per_page=${PRODUCTS_PER_PAGE}&page=${page}`);
        const products = response.data || [];
        const pagination = response.pagination || null;

        if (!products || products.length === 0) {
            container.innerHTML = '<div class="loading">Chưa có sản phẩm nào</div>';
            return;
        }

        renderProducts(container, products);

        if (pagination && paginationContainer) {
            renderPagination(paginationContainer, pagination);
            paginationContainer.style.display = 'flex';
        }
    } catch (error) {
        container.innerHTML = `
            <div class="loading">
                <p>⚠️ Không thể tải sản phẩm</p>
                <p style="font-size: 0.9rem;">${error.message}</p>
            </div>
        `;
    }
}

function selectAllProducts() {
    loadAllProducts(1);
}

// ============================================
// Products Page: Select Category
// ============================================
async function selectCategory(categoryId, categoryName) {
    currentCategoryId = categoryId;
    currentPage = 1;

    highlightCategory(categoryId);

    const title = document.getElementById('category-products-title');
    if (title) title.textContent = categoryName;

    await loadProductsByCategory(categoryId, 1);
}

function highlightCategory(id) {
    document.querySelectorAll('.category-card').forEach(card => card.classList.remove('active'));
    const activeCard = document.getElementById(`category-card-${id}`);
    if (activeCard) activeCard.classList.add('active');
}

async function loadProductsByCategory(categoryId, page) {
    const container = document.getElementById('category-products');
    const paginationContainer = document.getElementById('pagination-container');
    if (!container) return;

    container.innerHTML = '<div class="loading">Đang tải sản phẩm...</div>';
    if (paginationContainer) paginationContainer.style.display = 'none';

    try {
        const response = await getProductsByCategory(categoryId, page, PRODUCTS_PER_PAGE);
        const products = response.data || [];
        const pagination = response.pagination || null;

        if (!products || products.length === 0) {
            container.innerHTML = '<div class="loading">Không có sản phẩm nào trong danh mục này</div>';
            return;
        }

        renderProducts(container, products);

        if (pagination && paginationContainer) {
            renderPagination(paginationContainer, pagination);
            paginationContainer.style.display = 'flex';
        }
    } catch (error) {
        container.innerHTML = `
            <div class="loading">
                <p>⚠️ Không thể tải sản phẩm</p>
                <p style="font-size: 0.9rem;">${error.message}</p>
            </div>
        `;
    }
}

// ============================================
// Render Products
// ============================================
function renderProducts(container, products) {
    if (!products || products.length === 0) {
        container.innerHTML = '<div class="loading">Không có sản phẩm nào</div>';
        return;
    }

    container.innerHTML = products.map(product => {
        const imgSrc = product.image || 'https://via.placeholder.com/300x200?text=No+Image';
        const price = product.price || product.selling_price || 0;
        const name = product.name || 'Sản phẩm';
        const status = product.status || '';
        const escapedName = name.replace(/'/g, "\\'");

        return `
        <div class="product-card">
            <div class="product-image-wrapper">
                <img src="${imgSrc}" alt="${name}" loading="lazy">
                ${status === 'Hết hàng' ? '<span class="product-badge out-of-stock">Hết hàng</span>' : ''}
            </div>
            <div class="product-info">
                <h3 class="product-name">${name}</h3>
                <p class="product-price">${formatPrice(price)}</p>
                <button class="btn btn-secondary" onclick="handleAddToCart(${product.id}, '${escapedName}', ${price})" ${status === 'Hết hàng' ? 'disabled' : ''}>
                    🛒 Thêm vào giỏ
                </button>
            </div>
        </div>
    `;
    }).join('');
}

// ============================================
// Pagination
// ============================================
function renderPagination(container, pagination) {
    if (!container) return;

    const { current_page, last_page, total } = pagination;
    let html = '';

    html += `<button class="page-btn ${current_page <= 1 ? 'disabled' : ''}" 
                onclick="goToPage(${current_page - 1})" 
                ${current_page <= 1 ? 'disabled' : ''}>
                ‹ Trước
            </button>`;

    const pages = getPageNumbers(current_page, last_page);
    pages.forEach(p => {
        if (p === '...') {
            html += `<span class="page-ellipsis">…</span>`;
        } else {
            html += `<button class="page-btn ${p === current_page ? 'active' : ''}" 
                        onclick="goToPage(${p})">${p}</button>`;
        }
    });

    html += `<button class="page-btn ${current_page >= last_page ? 'disabled' : ''}" 
                onclick="goToPage(${current_page + 1})" 
                ${current_page >= last_page ? 'disabled' : ''}>
                Sau ›
            </button>`;

    html += `<span class="page-info">Trang ${current_page}/${last_page} (${total} SP)</span>`;

    container.innerHTML = html;
}

function getPageNumbers(current, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

    const pages = [1];
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
}

function goToPage(page) {
    if (page < 1) return;
    currentPage = page;

    if (currentCategoryId === '__all__') {
        loadAllProducts(page);
    } else if (currentCategoryId === '__all_home__') {
        loadHomePage(page);
    } else if (currentCategoryId) {
        loadProductsByCategory(currentCategoryId, page);
    }

    // Scroll lên section sản phẩm
    const section = document.getElementById('category-products-section') || document.getElementById('featured-products-section');
    if (section) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

async function loadHomePage(page) {
    const container = document.getElementById('featured-products');
    const paginationContainer = document.getElementById('pagination-container');
    if (!container) return;

    container.innerHTML = '<div class="loading">Đang tải sản phẩm...</div>';

    try {
        const response = await apiRequest(`/storefront/products?per_page=${PRODUCTS_PER_PAGE}&page=${page}`);
        const products = response.data || [];
        const pagination = response.pagination || null;

        renderProducts(container, products);

        if (pagination && pagination.last_page > 1 && paginationContainer) {
            renderPagination(paginationContainer, pagination);
            paginationContainer.style.display = 'flex';
        }
    } catch (error) {
        container.innerHTML = `<div class="loading"><p>⚠️ Không thể tải sản phẩm</p></div>`;
    }
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
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(price);
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed; bottom: 20px; right: 20px;
        background: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white; padding: 15px 25px; border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2); z-index: 1000;
        animation: slideIn 0.3s ease; font-family: 'Inter', sans-serif;
    `;
    notification.textContent = message;
    document.body.appendChild(notification);
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
    @keyframes slideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }
`;
document.head.appendChild(style);
