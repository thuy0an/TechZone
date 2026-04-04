/**
 * TechZone - Main Application
 * Frontend logic cho trang chủ và trang sản phẩm theo danh mục.
 */

const PRODUCTS_PER_PAGE = 8;
const HOME_CATEGORY_STRIP_LIMIT = 6;
const HOME_CATEGORY_SECTION_LIMIT = 3;
const HOME_CATEGORY_PRODUCTS_LIMIT = 4;

const state = {
    pageType: 'unknown',
    currentCategoryId: null,
    currentCategoryName: 'Tất cả sản phẩm',
    currentPage: 1,
    searchTerm: '',
    currentBrandId: null,
    minPrice: '',
    maxPrice: '',
    pendingFilters: {
        keyword: '',
        categoryId: null,
        brandId: null,
        minPrice: '',
        maxPrice: '',
    },
    advancedFilters: {
        keyword: '',
        categoryId: null,
        brandId: null,
        minPrice: '',
        maxPrice: '',
    },
    quickSearchTerm: '',
    searchDebounceTimer: null,
    sortBy: 'default',
    lastFetchedProducts: [],
    lastPagination: null,
    detailQuantity: 1,
    currentDetailProduct: null,
    relatedCarouselTimers: {},
    relatedCache: new Map(),
    relatedCacheTtlMs: 15 * 60 * 1000,
};

let homeCategoriesCache = null;
const homeCategoryState = new Map();

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

    if (document.getElementById('home-category-sections') || document.getElementById('home-category-strip')) {
        state.pageType = 'home';
        await initHomePage();
    }

    if (document.getElementById('category-products')) {
        state.pageType = 'products';
        await initProductsPage();
    }
});

async function initHomePage() {
    await renderHomeCategoryStrip();
    await renderHomeCategorySections();
}

async function initProductsPage() {
    setupProductsToolbar();
    applyProductsQueryFilters();
    renderProductsLoadingState();

    await Promise.allSettled([
        renderProductsPageCategories(),
        renderProductsPageBrands(),
        loadCurrentCategoryProducts(1),
    ]);
}

function setupProductsToolbar() {
    const sortSelect = document.getElementById('product-sort-select');
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const minPriceInput = document.getElementById('min-price-input');
    const maxPriceInput = document.getElementById('max-price-input');
    const resetButton = document.getElementById('filter-reset-btn');
    const advancedToggle = document.getElementById('advanced-search-toggle');
    const advancedPanel = document.getElementById('advanced-search-panel');
    const advancedContainer = document.getElementById('advanced-search');
    const advancedQuickInput = document.getElementById('advanced-search-quick-input');
    const advancedKeywordInput = document.getElementById('advanced-search-keyword');
    const advancedCategorySelect = document.getElementById('advanced-search-category');
    const advancedBrandSelect = document.getElementById('advanced-search-brand');
    const advancedMinPriceInput = document.getElementById('advanced-search-min');
    const advancedMaxPriceInput = document.getElementById('advanced-search-max');
    const advancedSubmitButton = document.getElementById('advanced-search-submit');
    const advancedResetButton = document.getElementById('advanced-search-reset');

    if (sortSelect) {
        sortSelect.addEventListener('change', (event) => {
            state.sortBy = event.target.value;
            renderCurrentProductsFromState();
        });
    }

    if (categorySelect) {
        categorySelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value;
            updateSidebarFilters({
                categoryId: selectedValue ? Number(selectedValue) : null,
            });
            applySidebarFilters();
        });
    }

    if (brandSelect) {
        brandSelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value;
            updateSidebarFilters({
                brandId: selectedValue ? Number(selectedValue) : null,
            });
            applySidebarFilters();
        });
    }

    if (minPriceInput) {
        minPriceInput.addEventListener('input', (event) => {
            updateSidebarFilters({
                minPrice: normalizeNumberString(event.target.value),
            });
            scheduleSidebarSearch();
        });
    }

    if (maxPriceInput) {
        maxPriceInput.addEventListener('input', (event) => {
            updateSidebarFilters({
                maxPrice: normalizeNumberString(event.target.value),
            });
            scheduleSidebarSearch();
        });
    }

    if (resetButton) {
        resetButton.addEventListener('click', () => resetFilters());
    }

    if (advancedToggle && advancedPanel) {
        advancedToggle.addEventListener('click', () => {
            const isOpen = advancedPanel.classList.contains('is-open');
            setAdvancedSearchOpen(!isOpen);
        });
    }

    if (advancedContainer) {
        document.addEventListener('click', (event) => {
            if (!advancedPanel || !advancedPanel.classList.contains('is-open')) return;
            if (advancedContainer.contains(event.target)) return;
            setAdvancedSearchOpen(false);
        });
    }

    if (advancedKeywordInput) {
        advancedKeywordInput.addEventListener('input', (event) => {
            updateAdvancedFilters({ keyword: event.target.value.trim() });
        });
    }

    if (advancedQuickInput) {
        advancedQuickInput.addEventListener('input', (event) => {
            state.quickSearchTerm = event.target.value.trim();
            scheduleQuickSearch();
        });
    }

    if (advancedCategorySelect) {
        advancedCategorySelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value;
            updateAdvancedFilters({
                categoryId: selectedValue ? Number(selectedValue) : null,
            });
        });
    }

    if (advancedBrandSelect) {
        advancedBrandSelect.addEventListener('change', (event) => {
            const selectedValue = event.target.value;
            updateAdvancedFilters({
                brandId: selectedValue ? Number(selectedValue) : null,
            });
        });
    }

    if (advancedMinPriceInput) {
        advancedMinPriceInput.addEventListener('input', (event) => {
            updateAdvancedFilters({
                minPrice: normalizeNumberString(event.target.value),
            });
        });
    }

    if (advancedMaxPriceInput) {
        advancedMaxPriceInput.addEventListener('input', (event) => {
            updateAdvancedFilters({
                maxPrice: normalizeNumberString(event.target.value),
            });
        });
    }

    if (advancedSubmitButton) {
        advancedSubmitButton.addEventListener('click', () => {
            applyAdvancedFilters();
        });
    }

    if (advancedResetButton) {
        advancedResetButton.addEventListener('click', () => resetFilters());
    }
}

function setAdvancedSearchOpen(isOpen) {
    const panel = document.getElementById('advanced-search-panel');
    const toggle = document.getElementById('advanced-search-toggle');
    if (!panel || !toggle) return;

    panel.classList.toggle('is-open', isOpen);
    panel.setAttribute('aria-hidden', String(!isOpen));
    toggle.setAttribute('aria-expanded', String(isOpen));
}

function updatePendingFilters(partial) {
    state.pendingFilters = {
        ...state.pendingFilters,
        ...partial,
    };
    syncFilterInputs(state.pendingFilters, state.advancedFilters);
}

function updateSidebarFilters(partial) {
    state.pendingFilters = {
        ...state.pendingFilters,
        ...partial,
    };
    syncFilterInputs(state.pendingFilters, state.advancedFilters);
}

function updateAdvancedFilters(partial) {
    state.advancedFilters = {
        ...state.advancedFilters,
        ...partial,
    };
    syncFilterInputs(state.pendingFilters, state.advancedFilters);
}

function syncFilterInputs(sidebarFilters, advancedFilters) {
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const minPriceInput = document.getElementById('min-price-input');
    const maxPriceInput = document.getElementById('max-price-input');
    const advancedKeywordInput = document.getElementById('advanced-search-keyword');
    const advancedCategorySelect = document.getElementById('advanced-search-category');
    const advancedBrandSelect = document.getElementById('advanced-search-brand');
    const advancedMinPriceInput = document.getElementById('advanced-search-min');
    const advancedMaxPriceInput = document.getElementById('advanced-search-max');

    if (categorySelect) categorySelect.value = sidebarFilters.categoryId ? String(sidebarFilters.categoryId) : '';
    if (brandSelect) brandSelect.value = sidebarFilters.brandId ? String(sidebarFilters.brandId) : '';
    if (minPriceInput) minPriceInput.value = sidebarFilters.minPrice || '';
    if (maxPriceInput) maxPriceInput.value = sidebarFilters.maxPrice || '';

    if (advancedKeywordInput) advancedKeywordInput.value = advancedFilters.keyword || '';
    if (advancedCategorySelect) advancedCategorySelect.value = advancedFilters.categoryId ? String(advancedFilters.categoryId) : '';
    if (advancedBrandSelect) advancedBrandSelect.value = advancedFilters.brandId ? String(advancedFilters.brandId) : '';
    if (advancedMinPriceInput) advancedMinPriceInput.value = advancedFilters.minPrice || '';
    if (advancedMaxPriceInput) advancedMaxPriceInput.value = advancedFilters.maxPrice || '';
}

function syncQuickSearchInput(value) {
    const advancedQuickInput = document.getElementById('advanced-search-quick-input');
    if (advancedQuickInput) advancedQuickInput.value = value || '';
}

function getFiltersFromInputs() {
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const minPriceInput = document.getElementById('min-price-input');
    const maxPriceInput = document.getElementById('max-price-input');
    const advancedKeywordInput = document.getElementById('advanced-search-keyword');
    const advancedCategorySelect = document.getElementById('advanced-search-category');
    const advancedBrandSelect = document.getElementById('advanced-search-brand');
    const advancedMinPriceInput = document.getElementById('advanced-search-min');
    const advancedMaxPriceInput = document.getElementById('advanced-search-max');

    const keyword = advancedKeywordInput?.value?.trim() || '';
    const categoryValue = advancedCategorySelect?.value ?? '';
    const brandValue = advancedBrandSelect?.value ?? '';
    const minValue = advancedMinPriceInput?.value ?? '';
    const maxValue = advancedMaxPriceInput?.value ?? '';

    return {
        keyword,
        categoryId: categoryValue ? Number(categoryValue) : null,
        brandId: brandValue ? Number(brandValue) : null,
        minPrice: normalizeNumberString(minValue),
        maxPrice: normalizeNumberString(maxValue),
    };
}

function getSelectedCategoryName() {
    const advancedCategorySelect = document.getElementById('advanced-search-category');
    if (!advancedCategorySelect) return 'Tất cả sản phẩm';
    if (!advancedCategorySelect.value) return 'Tất cả sản phẩm';
    return advancedCategorySelect.options[advancedCategorySelect.selectedIndex]?.text || 'Danh mục';
}

function applyFiltersFromInputs(options = {}) {
    const { closePanel = true } = options;
    const filters = getFiltersFromInputs();
    state.advancedFilters = { ...filters };

    state.searchTerm = filters.keyword;
    state.currentCategoryId = filters.categoryId;
    state.currentBrandId = filters.brandId;
    state.minPrice = filters.minPrice;
    state.maxPrice = filters.maxPrice;
    state.currentPage = 1;
    state.currentCategoryName = filters.categoryId ? getSelectedCategoryName() : 'Tất cả sản phẩm';

    state.pendingFilters = {
        keyword: state.searchTerm,
        categoryId: state.currentCategoryId,
        brandId: state.currentBrandId,
        minPrice: state.minPrice,
        maxPrice: state.maxPrice,
    };
    syncFilterInputs(state.pendingFilters, state.advancedFilters);
    highlightCategory(state.currentCategoryId);
    updateSectionTitle();
    loadCurrentCategoryProducts(1);

    if (closePanel) {
        setAdvancedSearchOpen(false);
    }
}

function applyAdvancedFilters() {
    applyFiltersFromInputs({ closePanel: true });
}

function getSidebarFilters() {
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const minPriceInput = document.getElementById('min-price-input');
    const maxPriceInput = document.getElementById('max-price-input');

    return {
        keyword: '',
        categoryId: categorySelect?.value ? Number(categorySelect.value) : null,
        brandId: brandSelect?.value ? Number(brandSelect.value) : null,
        minPrice: normalizeNumberString(minPriceInput?.value ?? ''),
        maxPrice: normalizeNumberString(maxPriceInput?.value ?? ''),
    };
}

function applySidebarFilters() {
    const filters = getSidebarFilters();
    state.pendingFilters = { ...filters };

    state.searchTerm = state.quickSearchTerm;
    state.currentCategoryId = filters.categoryId;
    state.currentBrandId = filters.brandId;
    state.minPrice = filters.minPrice;
    state.maxPrice = filters.maxPrice;
    state.currentPage = 1;
    state.currentCategoryName = filters.categoryId
        ? (document.getElementById('category-select')?.options[
            document.getElementById('category-select')?.selectedIndex
        ]?.text || 'Danh mục')
        : 'Tất cả sản phẩm';

    highlightCategory(state.currentCategoryId);
    updateSectionTitle();
    loadCurrentCategoryProducts(1);
}

function scheduleSidebarSearch() {
    if (state.searchDebounceTimer) {
        window.clearTimeout(state.searchDebounceTimer);
    }

    state.searchDebounceTimer = window.setTimeout(() => {
        applySidebarFilters();
    }, 300);
}

function scheduleQuickSearch() {
    if (state.searchDebounceTimer) {
        window.clearTimeout(state.searchDebounceTimer);
    }

    state.searchDebounceTimer = window.setTimeout(() => {
        applyQuickSearch();
    }, 300);
}

function applyQuickSearch() {
    state.searchTerm = state.quickSearchTerm;
    state.currentPage = 1;
    loadCurrentCategoryProducts(1);
}

function resetFilters() {
    state.searchTerm = '';
    state.currentCategoryId = null;
    state.currentCategoryName = 'Tất cả sản phẩm';
    state.currentBrandId = null;
    state.minPrice = '';
    state.maxPrice = '';
    state.sortBy = 'default';
    state.currentPage = 1;
    state.pendingFilters = {
        keyword: '',
        categoryId: null,
        brandId: null,
        minPrice: '',
        maxPrice: '',
    };
    state.advancedFilters = {
        keyword: '',
        categoryId: null,
        brandId: null,
        minPrice: '',
        maxPrice: '',
    };
    state.quickSearchTerm = '';

    const sortSelect = document.getElementById('product-sort-select');
    if (sortSelect) sortSelect.value = 'default';

    syncFilterInputs(state.pendingFilters, state.advancedFilters);
    syncQuickSearchInput(state.quickSearchTerm);

    highlightCategory();
    updateSectionTitle();
    loadCurrentCategoryProducts(1);
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
        container.innerHTML = '<button class="chip active" id="category-chip-all" onclick="selectAllProducts()">Tất cả</button>';
    }
}

async function renderHomeCategoryStrip() {
    const container = document.getElementById('home-category-strip');
    if (!container) return;

    try {
        const categories = await getHomeCategories();
        const visibleCategories = categories.slice(0, HOME_CATEGORY_STRIP_LIMIT);

        const tiles = visibleCategories.map(category => {
            const icon = getCategoryIcon(category.name);
            return `
                <a class="category-tile" href="products.html?category_id=${category.id}">
                    <div class="category-tile-icon">${icon}</div>
                    <span class="category-tile-label">${escapeHtml(category.name)}</span>
                </a>
            `;
        }).join('');

        container.innerHTML = `
            <div class="category-strip-grid">
                ${tiles || '<div class="loading">Khong co danh muc phu hop.</div>'}
            </div>
        `;
    } catch (error) {
        container.innerHTML = '<div class="loading">Khong the tai danh muc.</div>';
    }
}

async function renderHomeCategorySections() {
    const container = document.getElementById('home-category-sections');
    if (!container) return;

    try {
        const categories = await getHomeCategories();

        const limitedCategories = categories.slice(0, HOME_CATEGORY_SECTION_LIMIT);
        const sectionHtml = limitedCategories.map(category => {
            return `
                <section class="home-category-block" data-category-id="${category.id}">
                    <div class="home-category-carousel">
                        <button class="carousel-btn prev" type="button" data-carousel="cat-${category.id}" aria-label="Xem truoc">‹</button>
                        <div class="home-category-track" id="home-category-grid-${category.id}">
                            <div class="loading">Đang tải sản phẩm...</div>
                        </div>
                        <button class="carousel-btn next" type="button" data-carousel="cat-${category.id}" aria-label="Xem tiep">›</button>
                    </div>
                </section>
            `;
        }).join('');

        container.innerHTML = sectionHtml || '<div class="loading">Không có danh mục phù hợp.</div>';

        setupHomeCategoryLazyLoad(container);
    } catch (error) {
        container.innerHTML = '<div class="loading">Không thể tải danh mục.</div>';
    }
}

function applyProductsQueryFilters() {
    const params = new URLSearchParams(window.location.search);
    const categoryId = params.get('category_id');
    const brandId = params.get('brand_id');
    const keyword = params.get('keyword');

    if (categoryId) {
        state.currentCategoryId = Number(categoryId);
    }

    if (brandId) {
        state.currentBrandId = Number(brandId);
    }

    if (keyword) {
        state.searchTerm = keyword;
    }

    state.pendingFilters = {
        keyword: state.searchTerm || '',
        categoryId: state.currentCategoryId,
        brandId: state.currentBrandId,
        minPrice: state.minPrice || '',
        maxPrice: state.maxPrice || '',
    };
    state.advancedFilters = { ...state.pendingFilters };
    state.quickSearchTerm = state.searchTerm || '';

    syncFilterInputs(state.pendingFilters, state.advancedFilters);
    syncQuickSearchInput(state.quickSearchTerm);
    highlightCategory(state.currentCategoryId);

    updateSectionTitle();
}

async function loadHomeCategoryProducts(categoryId, options = {}) {
    const grid = document.getElementById(`home-category-grid-${categoryId}`);
    if (!grid) return;

    const { page = 1, append = false } = options;
    const categoryState = getHomeCategoryState(categoryId);
    if (categoryState.loading) return;
    categoryState.loading = true;

    if (!append) {
        grid.innerHTML = '<div class="loading">Đang tải sản phẩm...</div>';
    }

    try {
        const filters = {
            keyword: '',
            category_id: categoryId,
            brand_id: null,
            min_price: '',
            max_price: '',
        };
        const response = await searchStorefrontProductsAdvanced(filters, page, HOME_CATEGORY_PRODUCTS_LIMIT);
        const products = response.data || [];
        const pagination = response.pagination || null;

        categoryState.page = page;
        categoryState.hasMore = pagination ? pagination.current_page < pagination.last_page : products.length > 0;

        if (products.length === 0 && !append) {
            grid.innerHTML = '<div class="loading">Không có sản phẩm phù hợp.</div>';
            return;
        }

        const displayProducts = transformProductsForDisplay(products);
        if (append) {
            grid.insertAdjacentHTML('beforeend', renderProductsMarkup(displayProducts));
        } else {
            renderProducts(grid, displayProducts);
        }
    } catch (error) {
        if (!append) {
            grid.innerHTML = '<div class="loading">Không thể tải sản phẩm.</div>';
        }
    } finally {
        categoryState.loading = false;
        updateHomeCategoryButtons(categoryId);
    }
}

async function getHomeCategories() {
    if (Array.isArray(homeCategoriesCache)) {
        return homeCategoriesCache;
    }

    const response = await getStorefrontCategories();
    homeCategoriesCache = response.data || [];
    return homeCategoriesCache;
}

function setupHomeCategoryLazyLoad(container) {
    const sections = Array.from(container.querySelectorAll('.home-category-block'));
    if (!sections.length) return;

    if (!('IntersectionObserver' in window)) {
        sections.forEach(section => loadHomeCategorySection(section));
        return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            loadHomeCategorySection(entry.target);
            obs.unobserve(entry.target);
        });
    }, { root: null, rootMargin: '120px 0px', threshold: 0.1 });

    sections.forEach(section => observer.observe(section));
}

function loadHomeCategorySection(section) {
    if (!section || section.dataset.loaded === 'true') return;
    const categoryId = Number(section.dataset.categoryId);
    if (!categoryId) return;

    section.dataset.loaded = 'true';
    loadHomeCategoryProducts(categoryId, { page: 1, append: false }).then(() => setupHomeCategoryCarousel(categoryId));
}

function setupHomeCategoryCarousel(categoryId) {
    const container = document.getElementById(`home-category-grid-${categoryId}`);
    if (!container) return;

    container.classList.add('home-category-track');

    const prevBtn = document.querySelector(`.carousel-btn.prev[data-carousel="cat-${categoryId}"]`);
    const nextBtn = document.querySelector(`.carousel-btn.next[data-carousel="cat-${categoryId}"]`);

    const scrollStep = () => {
        const card = container.querySelector('.product-card');
        if (!card) return 0;
        const gap = parseInt(window.getComputedStyle(container).columnGap || '0', 10);
        return card.getBoundingClientRect().width + gap;
    };

    const updateButtons = () => {
        if (!prevBtn || !nextBtn) return;
    };

    if (prevBtn) {
        prevBtn.onclick = () => {
            const step = scrollStep();
            if (!step) return;
            container.scrollBy({ left: -step, behavior: 'smooth' });
        };
    }

    if (nextBtn) {
        nextBtn.onclick = () => {
            const categoryState = getHomeCategoryState(categoryId);
            if (!categoryState.hasMore || categoryState.loading) return;
            const nextPage = categoryState.page + 1;
            loadHomeCategoryProducts(categoryId, { page: nextPage, append: true }).then(() => {
                container.scrollTo({ left: container.scrollWidth, behavior: 'smooth' });
            });
        };
    }

    container.addEventListener('scroll', updateButtons);
    updateButtons();
    updateHomeCategoryButtons(categoryId);
}

function getHomeCategoryState(categoryId) {
    if (!homeCategoryState.has(categoryId)) {
        homeCategoryState.set(categoryId, { page: 0, hasMore: true, loading: false });
    }
    return homeCategoryState.get(categoryId);
}

function updateHomeCategoryButtons(categoryId) {
    const nextBtn = document.querySelector(`.carousel-btn.next[data-carousel="cat-${categoryId}"]`);
    const prevBtn = document.querySelector(`.carousel-btn.prev[data-carousel="cat-${categoryId}"]`);
    if (!nextBtn || !prevBtn) return;
    const categoryState = getHomeCategoryState(categoryId);
    const shouldHide = !categoryState.hasMore;
    nextBtn.style.display = shouldHide ? 'none' : '';
    prevBtn.style.display = shouldHide ? 'none' : '';
}

async function renderProductsPageCategories() {
    const categorySelect = document.getElementById('category-select');
    const advancedCategorySelect = document.getElementById('advanced-search-category');
    if (!categorySelect && !advancedCategorySelect) return;

    try {
        const response = await getStorefrontCategories();
        const categories = response.data || [];

        const options = [
            '<option value="">Tất cả sản phẩm</option>',
            ...categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`),
        ];

        if (categorySelect) {
            categorySelect.innerHTML = options.join('');
            categorySelect.value = state.pendingFilters.categoryId ? String(state.pendingFilters.categoryId) : '';
        }

        if (advancedCategorySelect) {
            const advancedOptions = [
                '<option value="">Tất cả danh mục</option>',
                ...categories.map(cat => `<option value="${cat.id}">${cat.name}</option>`),
            ];
            advancedCategorySelect.innerHTML = advancedOptions.join('');
            advancedCategorySelect.value = state.advancedFilters.categoryId ? String(state.advancedFilters.categoryId) : '';
        }
    } catch (error) {
        if (categorySelect) categorySelect.innerHTML = '<option value="">Tất cả sản phẩm</option>';
        if (advancedCategorySelect) advancedCategorySelect.innerHTML = '<option value="">Tất cả danh mục</option>';
    }
}

function renderProductsLoadingState() {
    const productContainer = document.getElementById('category-products') || document.getElementById('featured-products');
    if (!productContainer) return;

    productContainer.innerHTML = Array.from({ length: PRODUCTS_PER_PAGE }, () => `
        <article class="product-card skeleton-card" aria-hidden="true">
            <div class="product-image-wrapper">
                <div class="skeleton-block" style="width: 100%; height: 160px; border-radius: 16px;"></div>
            </div>
            <div class="product-info">
                <span class="skeleton-line w-80"></span>
                <span class="skeleton-line w-40"></span>
            </div>
        </article>
    `).join('');

    const paginationContainer = document.getElementById('pagination-container');
    if (paginationContainer) {
        paginationContainer.innerHTML = '';
        paginationContainer.classList.add('is-hidden');
    }

    const metaElement = document.getElementById('products-meta');
    if (metaElement) {
        metaElement.textContent = 'Đang tải sản phẩm...';
    }
}

async function renderProductsPageBrands() {
    const brandSelect = document.getElementById('brand-select');
    const advancedBrandSelect = document.getElementById('advanced-search-brand');
    if (!brandSelect && !advancedBrandSelect) return;

    try {
        const response = await getStorefrontBrands();
        const brands = response.data || [];

        const options = [
            '<option value="">Tất cả thương hiệu</option>',
            ...brands.map(brand => `<option value="${brand.id}">${brand.name}</option>`),
        ];

        if (brandSelect) {
            brandSelect.innerHTML = options.join('');
            brandSelect.value = state.pendingFilters.brandId ? String(state.pendingFilters.brandId) : '';
        }

        if (advancedBrandSelect) {
            const advancedOptions = [
                '<option value="">Tất cả thương hiệu</option>',
                ...brands.map(brand => `<option value="${brand.id}">${brand.name}</option>`),
            ];
            advancedBrandSelect.innerHTML = advancedOptions.join('');
            advancedBrandSelect.value = state.advancedFilters.brandId ? String(state.advancedFilters.brandId) : '';
        }
    } catch (error) {
        if (brandSelect) brandSelect.innerHTML = '<option value="">Tất cả thương hiệu</option>';
        if (advancedBrandSelect) advancedBrandSelect.innerHTML = '<option value="">Tất cả thương hiệu</option>';
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

    if (paginationContainer) {
        paginationContainer.classList.remove('is-hidden');
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
        console.error('Không thể tải sản phẩm:', error);
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
    container.innerHTML = renderProductsMarkup(products);
}

function renderProductsMarkup(products) {
    const hasDetailModal = Boolean(document.getElementById('product-detail-modal'));

    return products.map(product => {
        const price = Number(product.price ?? product.selling_price ?? 0);
        const image = resolveImageUrl(product.image);
        const name = product.name || 'Sản phẩm';
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

async function handleAddToCart(id, name, price, quantity = null) {
    if (typeof isLoggedIn === 'function' && !isLoggedIn()) {
        showNotification('Vui lòng đăng nhập để sử dụng giỏ hàng', 'error');
        try {
            const redirectUrl = window.location.pathname + window.location.search + window.location.hash;
            sessionStorage.setItem('post_login_redirect', redirectUrl);
        } catch (e) {
            // Ignore storage errors and proceed with redirect.
        }
        window.setTimeout(() => {
            window.location.href = 'login.html';
        }, 600);
        return;
    }

    const desiredQuantity = Math.max(1, Number(quantity || 1));

    try {
        await updateCartItem(id, desiredQuantity);
        await updateCartCount();
        showNotification(`Đã thêm "${name}" vào giỏ hàng`);
        if (typeof window.onProductAddedToCart === 'function') {
            window.onProductAddedToCart({ id, quantity: desiredQuantity });
        }
        closeProductDetail();
    } catch (error) {
        const message = error?.data?.message || error?.message || 'Không thể cập nhật giỏ hàng.';
        showNotification(message, 'error');
    }
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
    return `storage/${image}`;
}

function escapeSingleQuote(value) {
    return String(value).replace(/'/g, "\\'");
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    }).format(price || 0);
}

function getRelatedCacheKey(categoryId, brandId) {
    return `related:${categoryId || 'none'}:${brandId || 'none'}`;
}

function getCachedRelatedProducts(key) {
    const entry = state.relatedCache.get(key);
    if (!entry) return null;
    if (Date.now() > entry.expiresAt) {
        state.relatedCache.delete(key);
        return null;
    }
    return entry.items;
}

function setCachedRelatedProducts(key, items) {
    state.relatedCache.set(key, {
        items,
        expiresAt: Date.now() + state.relatedCacheTtlMs,
    });
}

function renderRelatedSkeletonCards(count = 6) {
    return Array.from({ length: count }, () => {
        return `
            <div class="related-card skeleton-card" aria-hidden="true">
                <div class="skeleton-thumb"></div>
                <div class="related-info">
                    <span class="skeleton-line w-70"></span>
                    <span class="skeleton-line w-40"></span>
                </div>
            </div>
        `;
    }).join('');
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
    const modal = document.querySelector('#product-detail-modal .product-detail-modal');
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

    if (modal) modal.classList.add('is-loading');
    if (title) title.innerHTML = '<span class="skeleton-line w-70"></span>';
    if (image) {
        image.removeAttribute('src');
        image.classList.add('skeleton-block');
    }
    if (price) price.innerHTML = '<span class="skeleton-line w-40"></span>';
    if (description) {
        description.innerHTML = [
            '<span class="skeleton-line w-100"></span>',
            '<span class="skeleton-line w-90"></span>',
            '<span class="skeleton-line w-60"></span>',
        ].join('');
    }
    if (brand) brand.innerHTML = '<span class="skeleton-line w-50"></span>';
    if (stock) {
        stock.textContent = '';
        stock.classList.remove('out');
    }
    if (stockQuantity) stockQuantity.innerHTML = '<span class="skeleton-line w-40"></span>';
    if (qtyValue) qtyValue.textContent = '1';
    if (specsList) {
        specsList.innerHTML = Array.from({ length: 4 }, () => {
            return `
                <div class="product-spec-item skeleton-card">
                    <span class="skeleton-line w-40"></span>
                    <span class="skeleton-line w-50"></span>
                </div>
            `;
        }).join('');
    }
    if (addButton) {
        addButton.disabled = true;
        addButton.onclick = null;
    }
    if (relatedList) relatedList.innerHTML = renderRelatedSkeletonCards(6);
    if (relatedSection) relatedSection.style.display = '';
    stopRelatedCarousels();
}

function renderProductDetailError(message) {
    const modal = document.querySelector('#product-detail-modal .product-detail-modal');
    const title = document.getElementById('product-detail-title');
    const description = document.getElementById('product-detail-description');
    const specsList = document.getElementById('product-detail-specs-list');
    const image = document.getElementById('product-detail-image');

    if (modal) modal.classList.remove('is-loading');
    if (image) {
        image.classList.remove('skeleton-block');
        image.src = 'https://via.placeholder.com/520x360?text=No+Data';
    }
    if (title) title.textContent = 'Không thể tải dữ liệu';
    if (description) description.textContent = message;
    if (specsList) {
        specsList.innerHTML = '<div class="product-spec-item"><span class="product-spec-key">Thông báo</span><span class="product-spec-value">Vui lòng thử lại</span></div>';
    }
}

function renderProductDetail(product) {
    const modal = document.querySelector('#product-detail-modal .product-detail-modal');
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

    if (modal) modal.classList.remove('is-loading');

    if (title) title.textContent = productName;
    if (image) {
        image.classList.remove('skeleton-block');
        image.src = resolvedImage;
    }
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

    const cacheKey = getRelatedCacheKey(product.category_id, product.brand_id);
    const cached = getCachedRelatedProducts(cacheKey);

    if (cached && cached.length) {
        listContainer.innerHTML = renderRelatedCards(cached);
        relatedSection.style.display = '';
        setupRelatedCarousel('related');
        return;
    }

    listContainer.innerHTML = renderRelatedSkeletonCards(6);

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

        if (merged.length) {
            setCachedRelatedProducts(cacheKey, merged);
        }

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
