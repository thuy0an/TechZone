/**
 * TechZone - Main Application
 * Frontend logic cho trang chủ và trang sản phẩm theo danh mục.
 */

const PRODUCTS_PER_PAGE = 8;

const state = {
    pageType: 'unknown',
    currentCategoryId: null,
    currentCategoryName: 'Tất cả sản phẩm',
    currentPage: 1,
    searchTerm: '',
    sortBy: 'default',
    lastFetchedProducts: [],
    lastPagination: null,
};

const CATEGORY_ICONS = {
    'điện thoại': '📱',
    'laptop': '💻',
    'tablet': '📟',
    'smartwatch': '⌚',
    'đồng hồ': '⌚',
    'phụ kiện': '🎧',
};

document.addEventListener('DOMContentLoaded', async () => {
    updateCartCount();

    if (document.getElementById('featured-products')) {
        state.pageType = 'home';
        await initHomePage();
    }

    if (document.getElementById('category-products')) {
        state.pageType = 'products';
        await initProductsPage();
    }
});

async function initHomePage() {
    await renderHomeCategoryTabs();
    await loadCurrentCategoryProducts(1);
}

async function initProductsPage() {
    setupProductsToolbar();
    await renderProductsPageCategories();
    await loadCurrentCategoryProducts(1);
}

function setupProductsToolbar() {
    const searchInput = document.getElementById('product-search-input');
    const sortSelect = document.getElementById('product-sort-select');
    const categorySelect = document.getElementById('category-select');

    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            state.searchTerm = event.target.value.trim().toLowerCase();
            renderCurrentProductsFromState();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', (event) => {
            state.sortBy = event.target.value;
            renderCurrentProductsFromState();
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value;

            if (!selectedValue) {
                selectAllProducts();
                return;
            }

            const selectedLabel = event.target.options[event.target.selectedIndex]?.text || 'Danh mục';
            selectCategory(Number(selectedValue), selectedLabel);
        });
    }
}

async function renderHomeCategoryTabs() {
    const container = document.getElementById('home-categories-list');
    if (!container) return;

    try {
        const response = await getStorefrontCategories();
        const categories = response.data || [];

        const allChip = `<button class="chip active" id="category-chip-all" onclick="selectAllProducts()">Tất cả</button>`;
        const chips = categories.map(cat => (
            `<button class="chip" id="category-chip-${cat.id}" onclick="selectCategory(${cat.id}, '${escapeSingleQuote(cat.name)}')">${cat.name}</button>`
        )).join('');

        container.innerHTML = allChip + chips;
    } catch (error) {
        container.innerHTML = '<span class="loading">Không tải được danh mục</span>';
    }
}

async function renderProductsPageCategories() {
    const categorySelect = document.getElementById('category-select');
    if (!categorySelect) return;

    try {
        const response = await getStorefrontCategories();
        const categories = response.data || [];

        const options = [
            '<option value="">Tất cả sản phẩm</option>',
            ...categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`),
        ];

        categorySelect.innerHTML = options.join('');
        categorySelect.value = '';
    } catch (error) {
        categorySelect.innerHTML = '<option value="">Không tải được danh mục</option>';
    }
}

function selectAllProducts() {
    state.currentCategoryId = null;
    state.currentCategoryName = 'Tất cả sản phẩm';
    state.currentPage = 1;
    highlightCategory();
    updateSectionTitle();
    loadCurrentCategoryProducts(1);
}

function selectCategory(categoryId, categoryName) {
    state.currentCategoryId = categoryId;
    state.currentCategoryName = categoryName;
    state.currentPage = 1;
    highlightCategory(categoryId);
    updateSectionTitle();
    loadCurrentCategoryProducts(1);
}

function highlightCategory(categoryId = null) {
    document.querySelectorAll('.category-card').forEach(card => card.classList.remove('active'));
    document.querySelectorAll('.chip').forEach(chip => chip.classList.remove('active'));

    const categorySelect = document.getElementById('category-select');
    if (categorySelect) {
        categorySelect.value = categoryId === null ? '' : String(categoryId);
    }

    if (categoryId === null) {
        document.getElementById('category-card-all')?.classList.add('active');
        document.getElementById('category-chip-all')?.classList.add('active');
        return;
    }

    document.getElementById(`category-card-${categoryId}`)?.classList.add('active');
    document.getElementById(`category-chip-${categoryId}`)?.classList.add('active');
}

function updateSectionTitle() {
    const homeTitle = document.getElementById('featured-products-title');
    const productTitle = document.getElementById('category-products-title');

    if (homeTitle) {
        homeTitle.textContent = state.currentCategoryId ? `Nổi bật: ${state.currentCategoryName}` : 'Sản phẩm nổi bật';
    }

    if (productTitle) {
        productTitle.textContent = state.currentCategoryName;
    }
}

async function loadCurrentCategoryProducts(page = 1) {
    state.currentPage = page;

    const productContainer = state.pageType === 'home'
        ? document.getElementById('featured-products')
        : document.getElementById('category-products');
    const paginationContainer = document.getElementById('pagination-container');

    if (!productContainer) return;

    productContainer.innerHTML = '<div class="loading">Đang tải sản phẩm...</div>';
    if (paginationContainer) {
        paginationContainer.classList.add('is-hidden');
    }

    try {
        const response = state.currentCategoryId === null
            ? await apiRequest(`/storefront/products?per_page=${PRODUCTS_PER_PAGE}&page=${page}`)
            : await getProductsByCategory(state.currentCategoryId, page, PRODUCTS_PER_PAGE);

        const products = response.data || [];
        const pagination = response.pagination || null;

        state.lastFetchedProducts = products;
        state.lastPagination = pagination;

        if (products.length === 0) {
            productContainer.innerHTML = '<div class="loading">Không có sản phẩm phù hợp.</div>';
            updateProductsMeta(0, 0, pagination);
            return;
        }

        renderCurrentProductsFromState();

        if (pagination && paginationContainer) {
            renderPagination(paginationContainer, pagination);
            paginationContainer.classList.remove('is-hidden');
        }
    } catch (error) {
        productContainer.innerHTML = `
            <div class="loading">
                <p>Không thể tải sản phẩm.</p>
                <p class="loading-detail">${error.message || 'Vui lòng thử lại sau.'}</p>
            </div>
        `;
    }
}

function renderCurrentProductsFromState() {
    const productContainer = state.pageType === 'home'
        ? document.getElementById('featured-products')
        : document.getElementById('category-products');

    if (!productContainer) return;

    const transformedProducts = transformProductsForDisplay(state.lastFetchedProducts);

    if (transformedProducts.length === 0) {
        productContainer.innerHTML = '<div class="loading">Không có sản phẩm khớp từ khóa trong trang này.</div>';
        updateProductsMeta(0, state.lastFetchedProducts.length, state.lastPagination);
        return;
    }

    renderProducts(productContainer, transformedProducts);
    updateProductsMeta(transformedProducts.length, state.lastFetchedProducts.length, state.lastPagination);
}

function transformProductsForDisplay(products) {
    const source = Array.isArray(products) ? [...products] : [];

    const filtered = state.searchTerm
        ? source.filter(item => String(item.name || '').toLowerCase().includes(state.searchTerm))
        : source;

    switch (state.sortBy) {
        case 'name_asc':
            filtered.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'vi'));
            break;
        case 'name_desc':
            filtered.sort((a, b) => String(b.name || '').localeCompare(String(a.name || ''), 'vi'));
            break;
        case 'price_asc':
            filtered.sort((a, b) => Number(a.price ?? a.selling_price ?? 0) - Number(b.price ?? b.selling_price ?? 0));
            break;
        case 'price_desc':
            filtered.sort((a, b) => Number(b.price ?? b.selling_price ?? 0) - Number(a.price ?? a.selling_price ?? 0));
            break;
        default:
            break;
    }

    return filtered;
}

function updateProductsMeta(visibleCount, pageCount, pagination) {
    const metaElement = document.getElementById('products-meta');
    if (!metaElement) return;

    if (!pagination) {
        metaElement.textContent = `Hiển thị ${visibleCount} sản phẩm`;
        return;
    }

    const isFiltered = state.searchTerm.length > 0;
    const filterText = isFiltered ? ` | Khớp từ khóa: ${visibleCount}/${pageCount}` : '';
    metaElement.textContent = `Trang ${pagination.current_page}/${pagination.last_page} | Tổng ${pagination.total} sản phẩm${filterText}`;
}

function renderProducts(container, products) {
    container.innerHTML = products.map(product => {
        const price = Number(product.price ?? product.selling_price ?? 0);
        const image = resolveImageUrl(product.image);
        const name = product.name || 'Sản phẩm';
        const escapedName = escapeSingleQuote(name);
        const isOutOfStock = Number(product.stock_quantity || 0) <= 0;

        return `
            <article class="product-card">
                <div class="product-image-wrapper">
                    <img src="${image}" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/400x260?text=No+Image'">
                    ${isOutOfStock ? '<span class="product-badge out-of-stock">Hết hàng</span>' : '<span class="product-badge in-stock">Sẵn hàng</span>'}
                </div>
                <div class="product-info">
                    <h3 class="product-name">${name}</h3>
                    <p class="product-price">${formatPrice(price)}</p>
                    <button class="btn btn-secondary" onclick="handleAddToCart(${product.id}, '${escapedName}', ${price})" ${isOutOfStock ? 'disabled' : ''}>
                        Thêm vào giỏ
                    </button>
                </div>
            </article>
        `;
    }).join('');
}

function renderPagination(container, pagination) {
    const current = pagination.current_page;
    const total = pagination.last_page;

    const prevBtn = `
        <button class="page-btn ${current <= 1 ? 'disabled' : ''}" onclick="goToPage(${current - 1})" ${current <= 1 ? 'disabled' : ''}>
            Trước
        </button>
    `;

    const nextBtn = `
        <button class="page-btn ${current >= total ? 'disabled' : ''}" onclick="goToPage(${current + 1})" ${current >= total ? 'disabled' : ''}>
            Sau
        </button>
    `;

    const pages = getPageNumbers(current, total).map(page => {
        if (page === '...') return '<span class="page-ellipsis">...</span>';
        return `<button class="page-btn ${page === current ? 'active' : ''}" onclick="goToPage(${page})">${page}</button>`;
    }).join('');

    container.innerHTML = `
        ${prevBtn}
        ${pages}
        ${nextBtn}
        <span class="page-info">Trang ${current}/${total} • ${pagination.total} sản phẩm</span>
    `;
}

function getPageNumbers(current, total) {
    if (total <= 7) {
        return Array.from({ length: total }, (_, index) => index + 1);
    }

    const pages = [1];
    if (current > 3) pages.push('...');

    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);

    for (let page = start; page <= end; page += 1) {
        pages.push(page);
    }

    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
}

function goToPage(page) {
    if (page < 1) return;
    loadCurrentCategoryProducts(page);

    const targetSection = document.getElementById('category-products-section') || document.getElementById('featured-products-section');
    targetSection?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function handleAddToCart(id, name, price) {
    addToCart({ id, name, price });
    showNotification(`Đã thêm "${name}" vào giỏ hàng`);
}

function getCategoryIcon(name) {
    const lowerName = String(name || '').toLowerCase();
    for (const key of Object.keys(CATEGORY_ICONS)) {
        if (lowerName.includes(key)) return CATEGORY_ICONS[key];
    }
    return '📦';
}

function resolveImageUrl(image) {
    if (!image) return 'https://via.placeholder.com/400x260?text=No+Image';
    if (String(image).startsWith('http') || String(image).startsWith('/')) return image;
    return `/storage/${image}`;
}

function escapeSingleQuote(value) {
    return String(value).replace(/'/g, "\\'");
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    }).format(price || 0);
}

function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `toast-notification ${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);

    window.setTimeout(() => {
        notification.classList.add('fade-out');
        window.setTimeout(() => notification.remove(), 240);
    }, 2000);
}
