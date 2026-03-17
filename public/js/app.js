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
    currentBrandId: null,
    minPrice: '',
    maxPrice: '',
    searchDebounceTimer: null,
    sortBy: 'default',
    lastFetchedProducts: [],
    lastPagination: null,
    detailQuantity: 1,
    currentDetailProduct: null,
    relatedCarouselTimers: {},
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

    if (document.getElementById('product-detail-modal')) {
        setupProductDetailModal();
    }

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
    await renderProductsPageBrands();
    await loadCurrentCategoryProducts(1);
}

function setupProductsToolbar() {
    const searchInput = document.getElementById('product-search-input');
    const sortSelect = document.getElementById('product-sort-select');
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const minPriceInput = document.getElementById('min-price-input');
    const maxPriceInput = document.getElementById('max-price-input');

    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            state.searchTerm = event.target.value.trim();
            scheduleSearchRefresh();
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

    if (brandSelect) {
        brandSelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value;
            state.currentBrandId = selectedValue ? Number(selectedValue) : null;
            state.currentPage = 1;
            loadCurrentCategoryProducts(1);
        });
    }

    if (minPriceInput) {
        minPriceInput.addEventListener('input', (event) => {
            state.minPrice = event.target.value;
            scheduleSearchRefresh();
        });
    }

    if (maxPriceInput) {
        maxPriceInput.addEventListener('input', (event) => {
            state.maxPrice = event.target.value;
            scheduleSearchRefresh();
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

async function renderProductsPageBrands() {
    const brandSelect = document.getElementById('brand-select');
    if (!brandSelect) return;

    try {
        const response = await getStorefrontBrands();
        const brands = response.data || [];

        const options = [
            '<option value="">Tất cả thương hiệu</option>',
            ...brands.map(brand => `<option value="${brand.id}">${brand.name}</option>`),
        ];

        brandSelect.innerHTML = options.join('');
        brandSelect.value = '';
    } catch (error) {
        brandSelect.innerHTML = '<option value="">Không tải được thương hiệu</option>';
    }
}

function scheduleSearchRefresh() {
    if (state.searchDebounceTimer) {
        window.clearTimeout(state.searchDebounceTimer);
    }

    state.searchDebounceTimer = window.setTimeout(() => {
        state.currentPage = 1;
        loadCurrentCategoryProducts(1);
    }, 350);
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
        const filters = buildSearchFilters();
        const response = await searchStorefrontProductsAdvanced(filters, page, PRODUCTS_PER_PAGE);

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

function buildSearchFilters() {
    return {
        keyword: state.searchTerm,
        category_id: state.currentCategoryId,
        brand_id: state.currentBrandId,
        min_price: state.minPrice,
        max_price: state.maxPrice,
    };
}

function renderCurrentProductsFromState() {
    const productContainer = state.pageType === 'home'
        ? document.getElementById('featured-products')
        : document.getElementById('category-products');

    if (!productContainer) return;

    const transformedProducts = transformProductsForDisplay(state.lastFetchedProducts);

    if (transformedProducts.length === 0) {
        productContainer.innerHTML = '<div class="loading">Không có sản phẩm phù hợp.</div>';
        updateProductsMeta(0, state.lastFetchedProducts.length, state.lastPagination);
        return;
    }

    renderProducts(productContainer, transformedProducts);
    updateProductsMeta(transformedProducts.length, state.lastFetchedProducts.length, state.lastPagination);
}

function transformProductsForDisplay(products) {
    const source = Array.isArray(products) ? [...products] : [];

    switch (state.sortBy) {
        case 'name_asc':
            source.sort((a, b) => String(a.name || '').localeCompare(String(b.name || ''), 'vi'));
            break;
        case 'name_desc':
            source.sort((a, b) => String(b.name || '').localeCompare(String(a.name || ''), 'vi'));
            break;
        case 'price_asc':
            source.sort((a, b) => Number(a.price ?? a.selling_price ?? 0) - Number(b.price ?? b.selling_price ?? 0));
            break;
        case 'price_desc':
            source.sort((a, b) => Number(b.price ?? b.selling_price ?? 0) - Number(a.price ?? a.selling_price ?? 0));
            break;
        default:
            break;
    }

    return source;
}

function updateProductsMeta(visibleCount, pageCount, pagination) {
    const metaElement = document.getElementById('products-meta');
    if (!metaElement) return;

    if (!pagination) {
        metaElement.textContent = `Hiển thị ${visibleCount} sản phẩm`;
        return;
    }

    const isFiltered = hasActiveFilters();
    const filterText = isFiltered ? ' | Đang lọc theo tiêu chí' : '';
    metaElement.textContent = `Trang ${pagination.current_page}/${pagination.last_page} | Tổng ${pagination.total} sản phẩm${filterText}`;
}

function hasActiveFilters() {
    return Boolean(
        state.searchTerm ||
        state.currentCategoryId ||
        state.currentBrandId ||
        state.minPrice ||
        state.maxPrice
    );
}

function renderProducts(container, products) {
    const hasDetailModal = Boolean(document.getElementById('product-detail-modal'));

    container.innerHTML = products.map(product => {
        const price = Number(product.price ?? product.selling_price ?? 0);
        const image = resolveImageUrl(product.image);
        const name = product.name || 'Sản phẩm';
        const escapedName = escapeSingleQuote(name);
        const isOutOfStock = Number(product.stock_quantity || 0) <= 0;

        const cardOpenHandler = hasDetailModal ? `onclick="openProductDetail(${product.id})"` : '';

        return `
            <article class="product-card" ${cardOpenHandler}>
                <div class="product-image-wrapper">
                    <img src="${image}" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/400x260?text=No+Image'">
                    ${isOutOfStock ? '<span class="product-badge out-of-stock">Hết hàng</span>' : '<span class="product-badge in-stock">Sẵn hàng</span>'}
                </div>
                <div class="product-info">
                    <h3 class="product-name">${name}</h3>
                    <p class="product-price">${formatPrice(price)}</p>
                    <button class="btn btn-secondary" onclick="event.stopPropagation(); handleAddToCart(${product.id}, '${escapedName}', ${price})" ${isOutOfStock ? 'disabled' : ''}>
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

function handleAddToCart(id, name, price, quantity = null) {
    const desiredQuantity = Math.max(1, Number(quantity || 1));
    addToCart({ id, name, price }, desiredQuantity);
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

function setupProductDetailModal() {
    const overlay = document.getElementById('product-detail-modal');
    if (!overlay) return;

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closeProductDetail();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeProductDetail();
        }
    });
}

async function openProductDetail(productId) {
    const overlay = document.getElementById('product-detail-modal');
    if (!overlay) return;

    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');

    setProductDetailLoading();

    try {
        const response = await getStorefrontProductDetail(productId);
        const product = response.data || response;
        renderProductDetail(product);
    } catch (error) {
        renderProductDetailError(error?.message || 'Không thể tải chi tiết sản phẩm.');
    }
}

function closeProductDetail() {
    const overlay = document.getElementById('product-detail-modal');
    if (!overlay) return;
    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');
    stopRelatedCarousels();
}

function setProductDetailLoading() {
    const title = document.getElementById('product-detail-title');
    const image = document.getElementById('product-detail-image');
    const price = document.getElementById('product-detail-price');
    const description = document.getElementById('product-detail-description');
    const brand = document.getElementById('product-detail-brand');
    const stock = document.getElementById('product-detail-stock');
    const stockQuantity = document.getElementById('product-detail-quantity');
    const qtyValue = document.getElementById('product-detail-qty-value');
    const specsList = document.getElementById('product-detail-specs-list');
    const addButton = document.getElementById('product-detail-add');
    const relatedList = document.getElementById('product-related-list');
    const relatedSection = document.getElementById('product-related');

    if (title) title.textContent = 'Đang tải...';
    if (image) image.src = 'https://via.placeholder.com/520x360?text=Loading';
    if (price) price.textContent = '';
    if (description) description.textContent = '';
    if (brand) brand.textContent = '';
    if (stock) {
        stock.textContent = '';
        stock.classList.remove('out');
    }
    if (stockQuantity) stockQuantity.textContent = '';
    if (qtyValue) qtyValue.textContent = '1';
    if (specsList) specsList.innerHTML = '';
    if (addButton) {
        addButton.disabled = true;
        addButton.onclick = null;
    }
    if (relatedList) relatedList.innerHTML = '<div class="loading">Đang tải gợi ý...</div>';
    if (relatedSection) relatedSection.style.display = '';
    stopRelatedCarousels();
}

function renderProductDetailError(message) {
    const title = document.getElementById('product-detail-title');
    const description = document.getElementById('product-detail-description');
    const specsList = document.getElementById('product-detail-specs-list');

    if (title) title.textContent = 'Không thể tải dữ liệu';
    if (description) description.textContent = message;
    if (specsList) {
        specsList.innerHTML = '<div class="product-spec-item"><span class="product-spec-key">Thông báo</span><span class="product-spec-value">Vui lòng thử lại</span></div>';
    }
}

function renderProductDetail(product) {
    const title = document.getElementById('product-detail-title');
    const image = document.getElementById('product-detail-image');
    const price = document.getElementById('product-detail-price');
    const description = document.getElementById('product-detail-description');
    const brand = document.getElementById('product-detail-brand');
    const stock = document.getElementById('product-detail-stock');
    const stockQuantity = document.getElementById('product-detail-quantity');
    const specsList = document.getElementById('product-detail-specs-list');
    const addButton = document.getElementById('product-detail-add');
    const qtyValue = document.getElementById('product-detail-qty-value');
    const qtyMinus = document.getElementById('product-detail-qty-minus');
    const qtyPlus = document.getElementById('product-detail-qty-plus');

    const resolvedImage = resolveImageUrl(product.image);
    const productName = product.name || 'Sản phẩm';
    const productPrice = Number(product.price ?? product.selling_price ?? 0);
    const availableQuantity = Number(product.stock_quantity || 0);
    const stockStatus = product.stock_status || (availableQuantity > 0 ? 'In Stock' : 'Out of Stock');
    const isOutOfStock = availableQuantity <= 0;

    state.currentDetailProduct = {
        id: product.id,
        name: productName,
        price: productPrice,
        unit: product.unit || 'sản phẩm',
        availableQuantity,
    };
    state.detailQuantity = 1;

    if (title) title.textContent = productName;
    if (image) image.src = resolvedImage;
    if (price) price.textContent = formatPrice(productPrice);
    if (description) description.textContent = product.description || 'Chưa có mô tả chi tiết.';
    if (brand) {
        const brandName = product.brand_name ? `Thương hiệu: ${product.brand_name}` : '';
        const categoryName = product.category_name ? `Danh mục: ${product.category_name}` : '';
        brand.textContent = [brandName, categoryName].filter(Boolean).join(' • ');
    }
    if (stock) {
        stock.textContent = stockStatus;
        stock.classList.toggle('out', isOutOfStock);
    }
    if (stockQuantity) {
        stockQuantity.textContent = `Còn ${availableQuantity} ${product.unit || 'sản phẩm'}`;
    }

    if (qtyValue) qtyValue.textContent = String(state.detailQuantity);
    if (qtyMinus) {
        qtyMinus.onclick = () => updateDetailQuantity(-1);
    }
    if (qtyPlus) {
        qtyPlus.onclick = () => updateDetailQuantity(1);
    }

    if (specsList) {
        specsList.innerHTML = renderSpecifications(product.specifications);
    }

    if (addButton) {
        addButton.disabled = isOutOfStock;
        addButton.onclick = () => {
            if (isOutOfStock) return;
            handleAddToCart(product.id, productName, productPrice, state.detailQuantity);
        };
    }

    loadRelatedProducts(product);
}

async function loadRelatedProducts(product) {
    const relatedSection = document.getElementById('product-related');
    const listContainer = document.getElementById('product-related-list');

    if (!relatedSection || !listContainer) return;

    try {
        const [categoryRes, brandRes] = await Promise.all([
            apiRequest(`/storefront/products?category_id=${product.category_id}&per_page=8`),
            apiRequest(`/storefront/products?brand_id=${product.brand_id}&per_page=8`),
        ]);

        const merged = [...(categoryRes.data || []), ...(brandRes.data || [])]
            .filter(item => item.id !== product.id)
            .reduce((acc, item) => {
                if (!acc.seen.has(item.id)) {
                    acc.seen.add(item.id);
                    acc.items.push(item);
                }
                return acc;
            }, { seen: new Set(), items: [] }).items;

        listContainer.innerHTML = renderRelatedCards(merged);
        relatedSection.style.display = merged.length ? '' : 'none';

        setupRelatedCarousel('related');
    } catch (error) {
        relatedSection.style.display = 'none';
    }
}

function setupRelatedCarousel(type) {
    const container = document.getElementById(`product-related-${type === 'related' ? 'list' : type}`);
    if (!container) return;

    const prevBtn = document.querySelector(`.carousel-btn.prev[data-carousel="${type}"]`);
    const nextBtn = document.querySelector(`.carousel-btn.next[data-carousel="${type}"]`);

    const scrollStep = () => {
        const card = container.querySelector('.related-card');
        if (!card) return 0;
        const gap = parseInt(window.getComputedStyle(container).columnGap || '0', 10);
        return card.getBoundingClientRect().width + gap;
    };

    const updateButtons = () => {
        if (!prevBtn || !nextBtn) return;
        const maxScrollLeft = container.scrollWidth - container.clientWidth - 1;
        prevBtn.disabled = container.scrollLeft <= 0;
        nextBtn.disabled = container.scrollLeft >= maxScrollLeft;
    };

    if (prevBtn) {
        prevBtn.onclick = (event) => {
            event.stopPropagation();
            container.scrollBy({ left: -scrollStep(), behavior: 'smooth' });
        };
    }

    if (nextBtn) {
        nextBtn.onclick = (event) => {
            event.stopPropagation();
            container.scrollBy({ left: scrollStep(), behavior: 'smooth' });
        };
    }

    container.addEventListener('scroll', updateButtons);
    updateButtons();

    setupDragScroll(container, type, scrollStep);
    startRelatedCarousel(type, container, scrollStep);
}

function startRelatedCarousel(type, container, stepFn) {
    stopRelatedCarousel(type);

    const interval = window.setInterval(() => {
        if (!container || !container.isConnected) {
            stopRelatedCarousel(type);
            return;
        }

        const step = stepFn();
        if (!step) return;

        const maxScrollLeft = container.scrollWidth - container.clientWidth;
        const nextLeft = container.scrollLeft + step;
        container.scrollTo({ left: nextLeft >= maxScrollLeft ? 0 : nextLeft, behavior: 'smooth' });
    }, 2800);

    state.relatedCarouselTimers[type] = interval;
}

function stopRelatedCarousel(type) {
    if (state.relatedCarouselTimers[type]) {
        window.clearInterval(state.relatedCarouselTimers[type]);
        delete state.relatedCarouselTimers[type];
    }
}

function stopRelatedCarousels() {
    Object.keys(state.relatedCarouselTimers).forEach(stopRelatedCarousel);
}

function setupDragScroll(container, type, stepFn) {
    let isDown = false;
    let startX = 0;
    let scrollLeft = 0;

    container.addEventListener('mousedown', (event) => {
        isDown = true;
        startX = event.pageX - container.offsetLeft;
        scrollLeft = container.scrollLeft;
        container.classList.add('dragging');
    });

    container.addEventListener('mouseleave', () => {
        isDown = false;
        container.classList.remove('dragging');
    });

    container.addEventListener('mouseup', () => {
        isDown = false;
        container.classList.remove('dragging');
    });

    container.addEventListener('mousemove', (event) => {
        if (!isDown) return;
        event.preventDefault();
        const x = event.pageX - container.offsetLeft;
        const walk = (x - startX) * 1.2;
        container.scrollLeft = scrollLeft - walk;
    });

    container.addEventListener('touchstart', () => stopRelatedCarousels());
    container.addEventListener('touchend', () => startRelatedCarousel(type, container, stepFn));
}

function renderRelatedCards(items) {
    if (!items || items.length === 0) {
        return '<div class="loading">Không có sản phẩm phù hợp.</div>';
    }

    return items.map(item => {
        const image = resolveImageUrl(item.image);
        const name = item.name || 'Sản phẩm';
        const price = Number(item.price ?? item.selling_price ?? 0);

        return `
            <button class="related-card" type="button" onclick="event.stopPropagation(); openProductDetail(${item.id})">
                <img src="${image}" alt="${name}" loading="lazy" onerror="this.src='https://via.placeholder.com/240x160?text=No+Image'">
                <div class="related-info">
                    <span class="related-name">${name}</span>
                    <span class="related-price">${formatPrice(price)}</span>
                </div>
            </button>
        `;
    }).join('');
}

function updateDetailQuantity(delta) {
    const product = state.currentDetailProduct;
    if (!product) return;

    const maxQty = Math.max(1, product.availableQuantity || 1);
    const next = Math.max(1, Math.min(maxQty, state.detailQuantity + delta));
    state.detailQuantity = next;

    const qtyValue = document.getElementById('product-detail-qty-value');
    if (qtyValue) qtyValue.textContent = String(next);
}

function renderSpecifications(specifications) {
    if (!specifications || (Array.isArray(specifications) && specifications.length === 0)) {
        return '<div class="product-spec-item"><span class="product-spec-key">Thông số</span><span class="product-spec-value">Chưa có dữ liệu</span></div>';
    }

    if (Array.isArray(specifications)) {
        return specifications.map((item) => {
            if (typeof item === 'string') {
                return `<div class="product-spec-item"><span class="product-spec-key">•</span><span class="product-spec-value">${item}</span></div>`;
            }

            const key = item.label || item.key || 'Thông số';
            const value = item.value ?? '';
            return `<div class="product-spec-item"><span class="product-spec-key">${key}</span><span class="product-spec-value">${value}</span></div>`;
        }).join('');
    }

    if (typeof specifications === 'object') {
        return Object.entries(specifications).map(([key, value]) => (
            `<div class="product-spec-item"><span class="product-spec-key">${key}</span><span class="product-spec-value">${value ?? ''}</span></div>`
        )).join('');
    }

    return '<div class="product-spec-item"><span class="product-spec-key">Thông số</span><span class="product-spec-value">Chưa có dữ liệu</span></div>';
}
