let cartSubtotalValue = 0;
let cartProductIds = [];
let addressBook = [];
let promotionClearTimer = null;
let promotionState = {
    code: null,
    promotionId: null,
    discount: 0,
    finalTotal: 0,
};

let selectedProductIds = new Set();
let hasUserSelection = false;

let lastOrderPayload = null;
let redirectTimer = null;
let hasSavedAddresses = false;
let centeredModalResolver = null;

const BANK_INFO = {
    bank_name: 'Vietcombank',
    account_number: '0021001234567',
    account_owner: 'CONG TY TECHZONE',
};

const promotionSuggestions = [];

async function loadPromotionSuggestions() {
    try {
        const res = await fetchActivePromotions();
        // API trả về { success, data: [...] } hoặc trực tiếp array
        const list = res.data || res;

        promotionSuggestions.length = 0;

        if (!Array.isArray(list)) return;

        list.forEach(p => {
            // API trả về quan hệ products là mảng object { id, name, code }
            // Cần lấy mảng ID từ đó
            let productIds = [];
            if (Array.isArray(p.products) && p.products.length > 0) {
                // Trường hợp API trả về products là [{id, name, code}, ...]
                productIds = p.products.map(prod => Number(prod.id || prod));
            } else if (Array.isArray(p.product_ids)) {
                // Trường hợp API trả về product_ids trực tiếp là [1, 2, 3]
                productIds = p.product_ids.map(Number);
            }

            promotionSuggestions.push({
                code: p.code,
                label: p.name,
                type: p.type,
                discount_value: p.discount_value,
                discount_unit: p.discount_unit,
                min_bill_value: Number(p.min_bill_value || 0),
                product_ids: productIds,
            });
        });
    } catch (e) {
        // Fallback: giữ rỗng, không crash
        console.warn('Không thể tải danh sách khuyến mãi:', e);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    initStorefrontLayout({ activePage: 'cart' });

    if (typeof isLoggedIn === 'function' && !isLoggedIn()) {
        try {
            const redirectUrl = window.location.pathname + window.location.search + window.location.hash;
            sessionStorage.setItem('post_login_redirect', redirectUrl);
        } catch (e) {
            // Ignore storage errors and proceed with redirect.
        }
        window.location.href = 'login.html';
        return;
    }

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', handleCheckout);
    }

    setupOrderSuccessModal();
    setupCenteredModal();
    window.onProductAddedToCart = async () => {
        await loadCart({ showStatus: false });
    };

    const promotionSelect = document.getElementById('promotion-select');
    if (promotionSelect) {
        promotionSelect.addEventListener('change', () => {
            const code = promotionSelect.value?.trim();
            if (code) {
                handleApplyPromotion(code);
            } else {
                clearPromotionState();
            }
        });
    }

    const saveNewAddressBtn = document.getElementById('new-address-save-btn');
    if (saveNewAddressBtn) {
        saveNewAddressBtn.addEventListener('click', handleCreateNewAddress);
    }

    // Tải địa chỉ và promotions song song; cart tải sau khi có promotions
    // để renderPromotionSelect() có dữ liệu ngay từ đầu
    loadUserAddresses();
    await loadPromotionSuggestions();
    loadCart({ showStatus: true });
    bindPaymentMethodListener();
    loadProvinces();
});

async function loadCart({ showStatus = false } = {}) {
    const status = document.getElementById('cart-status');
    const itemsContainer = document.getElementById('cart-items');

    if (showStatus && status) {
        status.textContent = 'Đang tải giỏ hàng...';
        status.classList.add('is-loading');
    }

    if (showStatus && itemsContainer) {
        itemsContainer.innerHTML = '';
    }

    try {
        const response = await getCart();
        const cart = response.data || response;
        renderCart(cart);
    } catch (error) {
        if (showStatus && status) {
            status.textContent = 'Khong the tai gio hang. Vui long thu lai.';
            status.classList.remove('is-loading');
        }
    }
}

function renderCart(cart) {
    const status = document.getElementById('cart-status');
    const itemsContainer = document.getElementById('cart-items');
    const subtotalElement = document.getElementById('cart-subtotal');
    const totalElement = document.getElementById('cart-total');

    const items = cart?.items || [];

    if (!itemsContainer) return;

    if (status) {
        status.textContent = '';
        status.classList.remove('is-loading');
    }

    if (items.length === 0) {
        itemsContainer.innerHTML = `
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                <h3>Giỏ hàng đang trống</h3>
                    <p>Hãy thêm sản phẩm để bắt đầu mua sắm.</p>
                        <a href="products.html" class="btn btn-primary">Khám phá sản phẩm</a>
                </div>
        `;
        cartSubtotalValue = 0;
        clearPromotionState();
        updateTotals(0, 0, subtotalElement, totalElement);
        toggleCheckoutAvailability(false);
        return;
    }

    normalizeSelectedIds(items);
    let subtotal = 0;

    itemsContainer.innerHTML = items.map(item => {
        const product = item.product || {};
        const currentPrice = Number(item.current_price ?? item.price_at_addition ?? product.selling_price ?? 0);
        const savedPrice = Number(item.price_at_addition ?? 0);
        const priceChanged = Boolean(item.is_price_changed);
        const oldPrice = Number(item.old_price ?? savedPrice);
        const quantity = Number(item.quantity || 0);
        const stock = Number(product.stock_quantity ?? 0);
        const isSelected = selectedProductIds.has(Number(product.id));
        const lineTotal = currentPrice * quantity;
        if (isSelected) subtotal += lineTotal;

        return `
            <article class="cart-item" data-product-id="${product.id}" data-unit-price="${currentPrice}" data-quantity="${quantity}" data-stock="${stock}">
                <div class="cart-item-select">
                    <input type="checkbox" class="cart-item-checkbox" ${isSelected ? 'checked' : ''} onchange="toggleCartItemSelection(${product.id}, this.checked)">
                </div>
                <div class="cart-item-media">
                    <button class="cart-item-link" type="button" onclick="openProductDetail(${product.id})">
                        <img src="${resolveImageUrl(product.image)}" alt="${escapeHtml(product.name || 'San pham')}" onerror="this.src='https://placehold.co/120x120?text=No+Image'">
                    </button>
                </div>
                <div class="cart-item-info">
                    <button class="cart-item-name" type="button" onclick="openProductDetail(${product.id})">
                        ${escapeHtml(product.name || 'San pham')}
                    </button>
                    <p class="cart-item-meta cart-item-price">
                        ${priceChanged ? `<span class="cart-item-price-old">${formatPrice(oldPrice)}</span>` : ''}
                        <span class="cart-item-price-current">${formatPrice(currentPrice)}</span>
                    </p>
                    <p class="cart-item-meta">Tồn Kho: ${product.stock_quantity ?? 0}</p>
                </div>
                <div class="cart-item-qty">
                    <button class="qty-btn" type="button" data-action="minus" onclick="changeQuantity(${product.id}, -1)">-</button>
                    <input class="cart-qty-input" type="number" min="1" max="${stock}" value="${quantity}"
                        onblur="handleQuantityInput(${product.id}, this)"
                        onkeydown="handleQuantityKeydown(event, ${product.id}, this)">
                    <button class="qty-btn" type="button" data-action="plus" onclick="changeQuantity(${product.id}, 1)" ${stock > 0 && quantity >= stock ? 'disabled' : ''}>+</button>
                </div>
                <div class="cart-item-total">
                    <span class="cart-item-line-total">${formatPrice(lineTotal)}</span>
                    <button class="btn btn-link" type="button" onclick="removeItem(${product.id})">Xóa</button>
                </div>
            </article>
        `;
    }).join('');

    cartSubtotalValue = subtotal;
    cartProductIds = getSelectedProductIds();
    updateTotals(subtotal, promotionState.discount || 0, subtotalElement, totalElement);
    toggleCheckoutAvailability(selectedProductIds.size > 0);
    renderPromotionSelect();
    updateSelectedSummary(items.length);
}

function normalizeSelectedIds(items) {
    const ids = items.map(item => Number(item.product?.id)).filter(Boolean);
    const available = new Set(ids);

    if (!hasUserSelection) {
        selectedProductIds = new Set(ids);
        return;
    }

    selectedProductIds.forEach(id => {
        if (!available.has(id)) selectedProductIds.delete(id);
    });
}

function getSelectedProductIds() {
    return Array.from(selectedProductIds);
}

function toggleCartItemSelection(productId, isChecked) {
    hasUserSelection = true;
    if (isChecked) {
        selectedProductIds.add(Number(productId));
    } else {
        selectedProductIds.delete(Number(productId));
    }

    updateTotalsFromDom();
}

function toggleSelectAll(isChecked) {
    hasUserSelection = true;
    const items = document.querySelectorAll('.cart-item');
    selectedProductIds.clear();

    if (isChecked) {
        items.forEach(item => {
            const productId = Number(item.dataset.productId || 0);
            if (productId) selectedProductIds.add(productId);
        });
    }

    items.forEach(item => {
        const checkbox = item.querySelector('.cart-item-checkbox');
        if (checkbox) checkbox.checked = isChecked;
    });

    updateTotalsFromDom();
}

async function changeQuantity(productId, delta) {
    const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    const currentQty = Number(itemRow?.dataset?.quantity || 0);
    if (delta < 0 && currentQty <= 1) {
        const shouldRemove = await showCenteredConfirm({
            title: 'Xóa sản phẩm',
            message: 'Bạn có muốn xóa sản phẩm khỏi giỏ hàng không?',
            confirmText: 'Xóa',
            cancelText: 'Giữ lại',
        });
        if (shouldRemove) {
            await removeItem(productId);
        }
        return;
    }

    const nextQty = currentQty + delta;
    await setCartItemQuantity(productId, nextQty);
}

function handleQuantityKeydown(event, productId, input) {
    if (event.key === 'Enter') {
        event.preventDefault();
        handleQuantityInput(productId, input);
    }
}

function handleQuantityInput(productId, input) {
    const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    const stock = Number(itemRow?.dataset?.stock || 0);
    const raw = Number(input?.value || 1);
    let desired = Number.isFinite(raw) ? Math.floor(raw) : 1;

    if (desired < 1) desired = 1;
    if (stock > 0 && desired > stock) {
        desired = stock;
        showCenteredModal({
            title: 'Thông báo',
            message: `Rất tiếc, bạn chỉ có thể mua tối đa ${stock} sản phẩm của shop hiện tại.`,
            confirmText: 'Đã hiểu',
        });
    }

    if (input) input.value = String(desired);
    setCartItemQuantity(productId, desired);
}

function updateQtyControls(itemRow) {
    if (!itemRow) return;
    const stock = Number(itemRow.dataset.stock || 0);
    const quantity = Number(itemRow.dataset.quantity || 0);
    const plusBtn = itemRow.querySelector('.qty-btn[data-action="plus"]');

    if (plusBtn) {
        plusBtn.disabled = stock > 0 && quantity >= stock;
    }
}

async function setCartItemQuantity(productId, desiredQty) {
    const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    if (!itemRow) return;

    const unitPrice = Number(itemRow.dataset.unitPrice || 0);
    const currentQty = Number(itemRow.dataset.quantity || 0);
    const clampedQty = Math.max(1, desiredQty);
    const delta = clampedQty - currentQty;

    if (delta === 0) return;

    itemRow.dataset.quantity = String(clampedQty);
    const lineTotalElement = itemRow.querySelector('.cart-item-line-total');
    if (lineTotalElement) lineTotalElement.textContent = formatPrice(unitPrice * clampedQty);
    const qtyInput = itemRow.querySelector('.cart-qty-input');
    if (qtyInput) qtyInput.value = String(clampedQty);
    updateQtyControls(itemRow);

    updateTotalsFromDom();
    clearPromotionState('Vui lòng áp dụng lại mã khuyến mãi sau khi thay đổi giỏ hàng.');

    try {
        await updateCartItem(productId, delta);
        await updateCartCount();
    } catch (error) {
        const message = error?.data?.message || error?.message || 'Khong the cap nhat gio hang.';
        showNotification(message, 'error');
        await loadCart({ showStatus: false });
    }
}

async function removeItem(productId) {
    const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    if (itemRow) {
        itemRow.remove();
        updateTotalsFromDom();
    }

    selectedProductIds.delete(Number(productId));

    clearPromotionState('Vui lòng áp dụng lại mã khuyến mãi sau khi thay đổi giỏ hàng.');

    try {
        await updateCartItem(productId, 0);
        await updateCartCount();
        showNotification('Đã xóa sản phẩm khỏi giỏ hàng.');
    } catch (error) {
        const message = error?.data?.message || error?.message || 'Không thể xóa sản phẩm.';
        showNotification(message, 'error');
        await loadCart({ showStatus: false });
    }
}

function updateTotalsFromDom() {
    const subtotalElement = document.getElementById('cart-subtotal');
    const totalElement = document.getElementById('cart-total');
    const items = document.querySelectorAll('.cart-item');
    let subtotal = 0;

    items.forEach(item => {
        const productId = Number(item.dataset.productId || 0);
        if (hasUserSelection && !selectedProductIds.has(productId)) return;
        const unitPrice = Number(item.dataset.unitPrice || 0);
        const quantity = Number(item.dataset.quantity || 0);
        subtotal += unitPrice * quantity;
    });

    cartSubtotalValue = subtotal;
    cartProductIds = getSelectedProductIds();
    updateTotals(subtotal, promotionState.discount || 0, subtotalElement, totalElement);
    toggleCheckoutAvailability(selectedProductIds.size > 0);
    renderPromotionSelect();
    updateSelectedSummary(items.length);

    const itemsContainer = document.getElementById('cart-items');
    if (items.length === 0 && itemsContainer) {
        itemsContainer.innerHTML = `
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                <h3>Gio hang dang trong</h3>
                <p>Hay them san pham de bat dau mua sam.</p>
                <a href="products.html" class="btn btn-primary">Kham pha san pham</a>
            </div>
        `;
    }
}

function updateSelectedSummary(totalItems) {
    const countElement = document.getElementById('cart-selected-count');
    const selectAllCheckbox = document.getElementById('cart-select-all');
    const selectedCount = selectedProductIds.size;

    if (countElement) {
        countElement.textContent = `${selectedCount} đã chọn`;
    }

    if (selectAllCheckbox) {
        if (totalItems === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedCount === totalItems) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }
}

function updateTotals(subtotal, discount, subtotalElement, totalElement) {
    const formattedSubtotal = formatPrice(subtotal);
    const formattedDiscount = formatPrice(discount || 0);
    const finalTotal = promotionState.promotionId ? promotionState.finalTotal : subtotal;
    const formattedTotal = formatPrice(finalTotal);
    const discountElement = document.getElementById('cart-discount');

    if (subtotalElement) subtotalElement.textContent = formattedSubtotal;
    if (discountElement) discountElement.textContent = formattedDiscount;
    if (totalElement) totalElement.textContent = formattedTotal;
}

function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    }).format(price || 0);
}

function resolveImageUrl(image) {
    if (!image) return 'https://placehold.co/120x120?text=No+Image';
    if (String(image).startsWith('http') || String(image).startsWith('/')) return image;
    return `storage/${image}`;
}

function escapeHtml(value) {
    return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
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

function setupCenteredModal() {
    const overlay = document.getElementById('centered-modal');
    const confirmBtn = document.getElementById('centered-modal-confirm');
    const cancelBtn = document.getElementById('centered-modal-cancel');

    if (!overlay || !confirmBtn || !cancelBtn) return;

    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            closeCenteredModal(false);
        }
    });

    confirmBtn.addEventListener('click', () => closeCenteredModal(true));
    cancelBtn.addEventListener('click', () => closeCenteredModal(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeCenteredModal(false);
        }
    });
}

function showCenteredModal({ title, message, confirmText = 'Đồng ý', variant = 'info' }) {
    const overlay = document.getElementById('centered-modal');
    const titleEl = document.getElementById('centered-modal-title');
    const messageEl = document.getElementById('centered-modal-message');
    const confirmBtn = document.getElementById('centered-modal-confirm');
    const cancelBtn = document.getElementById('centered-modal-cancel');
    const card = overlay?.querySelector('.center-modal-card');

    if (!overlay || !titleEl || !messageEl || !confirmBtn || !cancelBtn || !card) return;

    titleEl.textContent = title || 'Thông báo';
    messageEl.textContent = message || '';
    confirmBtn.textContent = confirmText;
    cancelBtn.classList.add('is-hidden');
    card.classList.remove('info');
    if (variant) card.classList.add(variant);

    overlay.classList.add('is-open');
    overlay.setAttribute('aria-hidden', 'false');
}

function showCenteredConfirm({ title, message, confirmText = 'Đồng ý', cancelText = 'Hủy', variant = 'info' }) {
    return new Promise(resolve => {
        centeredModalResolver = resolve;
        const overlay = document.getElementById('centered-modal');
        const titleEl = document.getElementById('centered-modal-title');
        const messageEl = document.getElementById('centered-modal-message');
        const confirmBtn = document.getElementById('centered-modal-confirm');
        const cancelBtn = document.getElementById('centered-modal-cancel');
        const card = overlay?.querySelector('.center-modal-card');

        if (!overlay || !titleEl || !messageEl || !confirmBtn || !cancelBtn || !card) {
            centeredModalResolver = null;
            resolve(false);
            return;
        }

        titleEl.textContent = title || 'Xác nhận';
        messageEl.textContent = message || '';
        confirmBtn.textContent = confirmText;
        cancelBtn.textContent = cancelText;
        cancelBtn.classList.remove('is-hidden');
        card.classList.remove('info');
        if (variant) card.classList.add(variant);

        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
    });
}

function closeCenteredModal(result) {
    const overlay = document.getElementById('centered-modal');
    if (!overlay) return;

    overlay.classList.remove('is-open');
    overlay.setAttribute('aria-hidden', 'true');

    if (typeof centeredModalResolver === 'function') {
        const resolver = centeredModalResolver;
        centeredModalResolver = null;
        resolver(Boolean(result));
    }
}


function toggleAddressForm() {
    const option = document.querySelector('input[name="address_option"]:checked').value;
    if (option === 'existing') {
        document.getElementById('existing-address-block').style.display = 'block';
        document.getElementById('new-address-block').style.display = 'none';
    } else {
        document.getElementById('existing-address-block').style.display = 'none';
        document.getElementById('new-address-block').style.display = 'block';
    }
}

async function loadUserAddresses(selectedAddressId = null) {
    try {
        const response = await apiRequest('/storefront/addresses', { method: 'GET' });

        const addresses = response.data;
        addressBook = Array.isArray(addresses) ? addresses : [];
        hasSavedAddresses = addressBook.length > 0;
        const select = document.getElementById('existing-address-block');
        select.innerHTML = '';

        if (!addresses || addresses.length === 0) {
            const newOption = document.getElementById('opt_new');
            const existingOption = document.getElementById('opt_existing');
            if (existingOption) {
                existingOption.disabled = true;
            }
            if (newOption) {
                newOption.checked = true;
            }
            toggleAddressForm();
            select.innerHTML = '<div class="empty-state">Bạn chưa có địa chỉ nào được lưu. Vui lòng nhập địa chỉ mới để tiếp tục.</div>';
        } else {
            addresses.forEach(addr => {
                const isChecked = addr.is_default ? 'checked' : '';
                const defaultBadge = addr.is_default ? '<span class="default-badge">Mặc định</span>' : '';

                const cardHtml = `
                    <div class="address-item">
                        <input type="radio" name="selected_existing_address" id="addr_${addr.id}" value="${addr.id}" class="address-radio" ${isChecked}>
                        <label for="addr_${addr.id}" class="address-label">
                            <div class="address-name">
                                <span>${addr.receiver_name} - ${addr.receiver_phone}</span>
                                ${defaultBadge}
                            </div>
                            <p class="address-detail">
                                ${addr.address}
                            </p>
                        </label>
                    </div>
                `;

                select.insertAdjacentHTML('beforeend', cardHtml);
            });

            document.getElementById('opt_existing').disabled = false;
            document.getElementById('opt_existing').click();

            if (selectedAddressId) {
                const selectedRadio = document.getElementById(`addr_${selectedAddressId}`);
                if (selectedRadio) {
                    selectedRadio.checked = true;
                }
            }
        }
    } catch (error) {
        console.error("Lỗi khi tải địa chỉ:", error);
        hasSavedAddresses = false;
        const select = document.getElementById('existing-address-block');
        if (select) {
            select.innerHTML = '<div class="error-text">Không thể tải địa chỉ</div>';
        }
    }
}

async function loadProvinces() {
    try {
        const data = await apiRequest('/locations/provinces');

        const citySelect = document.getElementById('new_city');
        citySelect.innerHTML = '<option value="">Chọn Tỉnh/Thành phố</option>';

        data.data.forEach(province => {
            let option = document.createElement('option');
            option.value = province.id;
            option.text = province.name;
            citySelect.appendChild(option);
        });
    } catch (error) {
        console.error("Lỗi khi tải danh sách Tỉnh/Thành phố:", error);
    }
}

async function loadDistricts() {
    const provinceId = document.getElementById('new_city').value;
    const districtSelect = document.getElementById('new_district');
    const wardSelect = document.getElementById('new_ward');

    districtSelect.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    districtSelect.disabled = true;
    wardSelect.disabled = true;

    if (!provinceId) return;

    try {
        const data = await apiRequest(`/locations/districts?province_id=${provinceId}`);
        data.data.forEach(district => {
            let option = document.createElement('option');
            option.value = district.id;
            option.text = district.name;
            districtSelect.appendChild(option);
        });
        districtSelect.disabled = false;
    } catch (error) {
        console.error("Lỗi khi tải danh sách Quận/Huyện:", error);
    }
}

async function loadWards() {
    const districtId = document.getElementById('new_district').value;
    const wardSelect = document.getElementById('new_ward');

    wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
    wardSelect.disabled = true;

    if (!districtId) return;

    try {
        const data = await apiRequest(`/locations/wards?district_id=${districtId}`);
        data.data.forEach(ward => {
            let option = document.createElement('option');
            option.value = ward.id;
            option.text = ward.name;
            wardSelect.appendChild(option);
        });
        wardSelect.disabled = false;
    } catch (error) {
        console.error("Lỗi khi tải danh sách Phường/Xã:", error);
    }
}

function renderPromotionSelect() {
    const select = document.getElementById('promotion-select');
    if (!select) return;

    const currentCode = select.value;
    select.innerHTML = '<option value="">-- Chọn mã khuyến mãi --</option>';

    if (promotionSuggestions.length === 0) {
        const opt = document.createElement('option');
        opt.disabled = true;
        opt.textContent = '(Không có mã khuyến mãi đang hoạt động)';
        select.appendChild(opt);
        return;
    }

    promotionSuggestions.forEach(promo => {
        const eligible = isPromotionEligible(promo);
        const opt = document.createElement('option');
        opt.value = promo.code;
        opt.disabled = !eligible;

        const discountText = promo.discount_unit === 'percent'
            ? `${promo.discount_value}%`
            : formatPrice(promo.discount_value);
        const minHint = !eligible && promo.min_bill_value > 0
            ? ` | Đơn tối thiểu ${formatPrice(promo.min_bill_value)}`
            : '';
        const lockIcon = !eligible ? ' 🔒' : '';

        opt.textContent = `${promo.label} — Giảm ${discountText}${minHint} (${promo.code})${lockIcon}`;
        select.appendChild(opt);
    });

    if (currentCode) select.value = currentCode;
}

function isPromotionEligible(promo) {
    // Check min_bill_value
    if (promo.min_bill_value > 0 && cartSubtotalValue < promo.min_bill_value) return false;

    // Với discount_by_product: phải có ít nhất 1 sản phẩm trong giỏ khớp
    if (promo.type === 'discount_by_product') {
        if (!promo.product_ids || promo.product_ids.length === 0) return false;
        return promo.product_ids.some(id => cartProductIds.includes(Number(id)));
    }

    return true;
}

function setActivePromotionChip(code) {
    const select = document.getElementById('promotion-select');
    if (!select) return;
    if (!code) {
        select.value = '';
    } else {
        select.value = code;
    }
}

function setPromotionFeedback(message, type = 'success') {
    const feedback = document.getElementById('promotion-feedback');
    if (!feedback) return;

    feedback.textContent = message || '';
    feedback.classList.remove('success', 'error', 'show');

    if (message) {
        feedback.classList.add('show', type === 'error' ? 'error' : 'success');
    }
}

function clearPromotionState(message) {
    promotionState = {
        code: null,
        promotionId: null,
        discount: 0,
        finalTotal: 0,
    };
    setActivePromotionChip(null);
    if (message) {
        setPromotionFeedback(message, 'error');
    } else {
        setPromotionFeedback('', 'success');
    }

    updateTotals(cartSubtotalValue, 0, document.getElementById('cart-subtotal'), document.getElementById('cart-total'));
}

async function handleApplyPromotion(code) {
    const cleanCode = String(code || '').trim();
    if (!cleanCode) {
        setPromotionFeedback('Vui lòng chọn mã khuyến mãi để áp dụng.', 'error');
        return;
    }

    const select = document.getElementById('promotion-select');
    if (select) select.disabled = true;

    try {
        const response = await applyPromotion(cleanCode, getSelectedProductIds());
        const payload = response.data || response;

        promotionState = {
            code: cleanCode,
            promotionId: payload.promotion_id,
            discount: Number(payload.discount_amount || 0),
            finalTotal: Number(payload.final_total || 0),
        };

        updateTotals(cartSubtotalValue, promotionState.discount, document.getElementById('cart-subtotal'), document.getElementById('cart-total'));
        setActivePromotionChip(cleanCode);
        setPromotionFeedback(`Đã áp dụng mã ${cleanCode}. Bạn được giảm ${formatPrice(promotionState.discount)}.`, 'success');
    } catch (error) {
        const message = error?.data?.errors || error?.data?.message || error?.message || 'Không thể áp dụng mã khuyến mãi.';
        clearPromotionState();
        setPromotionFeedback(message, 'error');
    } finally {
        if (select) select.disabled = false;
    }
}

function toggleCheckoutAvailability(isEnabled) {
    const checkoutBtn = document.getElementById('checkout-btn');
    if (!checkoutBtn) return;

    checkoutBtn.disabled = !isEnabled;
    checkoutBtn.textContent = isEnabled ? 'Tiến hành thanh toán' : 'Giỏ hàng đang trống';
}

function getSelectedPaymentMethod() {
    const selected = document.querySelector('input[name="payment_method"]:checked');
    return selected ? selected.value : 'cash';
}

function bindPaymentMethodListener() {
    const inputs = document.querySelectorAll('input[name="payment_method"]');
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            renderPaymentInfo(input.value);
        });
    });

    const current = getSelectedPaymentMethod();
    renderPaymentInfo(current);
}

function renderPaymentInfo(method, responseInfo = null) {
    const container = document.getElementById('payment-info');
    if (!container) return;

    container.classList.remove('show');
    container.innerHTML = '';

    if (method === 'bank_transfer') {
        const info = responseInfo || BANK_INFO;
        container.innerHTML = `
            <div><strong>Ngân hàng:</strong> ${info.bank_name}</div>
            <div><strong>STK:</strong> ${info.account_number}</div>
            <div><strong>Chủ TK:</strong> ${info.account_owner}</div>
            ${responseInfo?.transfer_note ? `<div><strong>Nội dung:</strong> ${responseInfo.transfer_note}</div>` : ''}
        `;
        container.classList.add('show');
        return;
    }

    if (method === 'online') {
        container.innerHTML = '<div>He thong dang xu ly thanh toan va se chuyen sang trang tom tat don hang.</div>';
        container.classList.add('show');
    }
}

function setupOrderSuccessModal() {
    const modal = document.getElementById('order-success-modal');
    if (!modal) return;

    modal.addEventListener('click', (event) => {
        const target = event.target;
        const closeId = target?.getAttribute?.('data-close');
        if (closeId === 'order-success-modal') {
            closeOrderSuccessModal();
        }
    });

    const summaryBtn = document.getElementById('order-summary-btn');
    if (summaryBtn) {
        summaryBtn.addEventListener('click', () => {
            if (lastOrderPayload?.order_id) {
                window.location.href = `order-summary.html?id=${lastOrderPayload.order_id}`;
            }
        });
    }
}

function openOrderSuccessModal(payload) {
    const modal = document.getElementById('order-success-modal');
    const content = document.getElementById('order-success-content');
    if (!modal || !content) return;

    lastOrderPayload = payload;
    const paymentLabel = getPaymentLabel(payload.payment_method);
    const totalText = formatPrice(payload.total_amount || payload.final_total || 0);

    content.innerHTML = `
        <div class="order-modal-row">
            <span>Mã đơn</span>
            <span>${payload.order_code || `#${payload.order_id}`}</span>
        </div>
        <div class="order-modal-row">
            <span>Tổng tiền</span>
            <span>${totalText}</span>
        </div>
        <div class="order-modal-row">
            <span>Thanh toán</span>
            <span>${paymentLabel}</span>
        </div>
    `;

    if (payload.bank_transfer_info) {
        content.insertAdjacentHTML('beforeend', `
            <div class="order-modal-row">
                <span>Ngân hàng</span>
                <span>${payload.bank_transfer_info.bank_name}</span>
            </div>
            <div class="order-modal-row">
                <span>STK</span>
                <span>${payload.bank_transfer_info.account_number}</span>
            </div>
            <div class="order-modal-row">
                <span>Chủ TK</span>
                <span>${payload.bank_transfer_info.account_owner}</span>
            </div>
            <div class="order-modal-row">
                <span>Nội dung</span>
                <span>${payload.bank_transfer_info.transfer_note}</span>
            </div>
        `);
    }

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');

    if (redirectTimer) {
        clearTimeout(redirectTimer);
    }
    redirectTimer = window.setTimeout(() => {
        if (payload.order_id) {
            window.location.href = `order-summary.html?id=${payload.order_id}`;
        }
    }, 5000);
}

function closeOrderSuccessModal() {
    const modal = document.getElementById('order-success-modal');
    if (!modal) return;

    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (redirectTimer) {
        clearTimeout(redirectTimer);
        redirectTimer = null;
    }
}

function resetCheckoutUI() {
    const paymentInfo = document.getElementById('payment-info');
    if (paymentInfo) {
        paymentInfo.classList.remove('show');
        paymentInfo.innerHTML = '';
    }

    const promotionInput = document.getElementById('promotion-code-input');
    if (promotionInput) {
        promotionInput.value = '';
    }

    setPromotionFeedback('', 'success');
    clearPromotionState();

    const cashOption = document.querySelector('input[name="payment_method"][value="cash"]');
    if (cashOption) {
        cashOption.checked = true;
        renderPaymentInfo('cash');
    }
}

function getPaymentLabel(method) {
    if (method === 'bank_transfer') return 'Chuyển khoản ngân hàng';
    if (method === 'online') return 'Thanh toán online';
    return 'Thanh toán khi nhận hàng';
}

function getSelectedAddress() {
    const option = document.querySelector('input[name="address_option"]:checked')?.value;
    if (option === 'new') {
        const receiverName = document.getElementById('new_receiver_name')?.value?.trim();
        const receiverPhone = document.getElementById('new_phone')?.value?.trim();
        const addressLine = document.getElementById('new_address')?.value?.trim();
        const cityEl = document.getElementById('new_city');
        const districtEl = document.getElementById('new_district');
        const wardEl = document.getElementById('new_ward');

        const cityName = cityEl?.selectedOptions?.[0]?.text || '';
        const districtName = districtEl?.selectedOptions?.[0]?.text || '';
        const wardName = wardEl?.selectedOptions?.[0]?.text || '';
        const provinceId = cityEl?.value || null;
        const districtId = districtEl?.value || null;
        const wardCode = wardEl?.value || null;

        if (!receiverName || !receiverPhone || !addressLine || !provinceId || !districtId || !wardCode) {
            return { error: 'Vui lòng nhập đầy đủ thông tin địa chỉ mới (tỉnh, quận, phường, địa chỉ).' };
        }

        const shippingAddressParts = [addressLine, wardName, districtName, cityName].filter(Boolean);
        return {
            receiver_name: receiverName,
            receiver_phone: receiverPhone,
            shipping_address: shippingAddressParts.join(', '),
            province_id: provinceId ? Number(provinceId) : null,
            district_id: districtId ? Number(districtId) : null,
            ward_code: wardCode || null,
            province_name: cityName || null,
            district_name: districtName || null,
            ward_name: wardName || null,
        };
    }

    const selectedExisting = document.querySelector('input[name="selected_existing_address"]:checked');
    const selectedId = selectedExisting ? Number(selectedExisting.value) : null;

    if (!hasSavedAddresses) {
        return { error: 'Bạn chưa có địa chỉ lưu. Vui lòng nhập địa chỉ mới để tiếp tục.' };
    }

    if (!selectedId) {
        return { error: 'Vui lòng chọn địa chỉ giao hàng.' };
    }

    return {
        user_address_id: selectedId,
    };
}

function buildNewAddressPayload() {
    const receiverName = document.getElementById('new_receiver_name')?.value?.trim();
    const receiverPhone = document.getElementById('new_phone')?.value?.trim();
    const addressLine = document.getElementById('new_address')?.value?.trim();
    const cityEl = document.getElementById('new_city');
    const districtEl = document.getElementById('new_district');
    const wardEl = document.getElementById('new_ward');

    const cityName = cityEl?.selectedOptions?.[0]?.text || '';
    const districtName = districtEl?.selectedOptions?.[0]?.text || '';
    const wardName = wardEl?.selectedOptions?.[0]?.text || '';
    const provinceId = cityEl?.value || null;
    const districtId = districtEl?.value || null;
    const wardCode = wardEl?.value || null;

    if (!receiverName || !receiverPhone || !addressLine || !provinceId || !districtId || !wardCode) {
        return { error: 'Vui lòng nhập đầy đủ thông tin địa chỉ mới (tỉnh, quận, phường, địa chỉ).' };
    }

    const fullAddress = [addressLine, wardName, districtName, cityName].filter(Boolean).join(', ');
    return {
        receiver_name: receiverName,
        receiver_phone: receiverPhone,
        address: fullAddress,
    };
}

function resetNewAddressForm() {
    const fields = ['new_receiver_name', 'new_phone', 'new_address'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    const cityEl = document.getElementById('new_city');
    const districtEl = document.getElementById('new_district');
    const wardEl = document.getElementById('new_ward');
    if (cityEl) cityEl.value = '';
    if (districtEl) {
        districtEl.innerHTML = '<option value="">Chọn Quận/Huyện</option>';
        districtEl.disabled = true;
    }
    if (wardEl) {
        wardEl.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wardEl.disabled = true;
    }
}

async function handleCreateNewAddress() {
    const payload = buildNewAddressPayload();
    if (payload.error) {
        showNotification(payload.error, 'error');
        return;
    }

    const saveBtn = document.getElementById('new-address-save-btn');
    if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Đang lưu...';
    }

    try {
        const response = await apiRequest('/storefront/addresses', {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        const created = response.data || response;
        showNotification('Đã lưu địa chỉ mới.');
        resetNewAddressForm();
        await loadUserAddresses(created?.id || null);

        const existingOption = document.getElementById('opt_existing');
        if (existingOption) {
            existingOption.checked = true;
        }
        toggleAddressForm();
    } catch (error) {
        const message = error?.data?.message || error?.message || 'Không thể lưu địa chỉ mới.';
        showNotification(message, 'error');
    } finally {
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Lưu địa chỉ';
        }
    }
}

async function handleCheckout() {
    if (cartSubtotalValue <= 0 || selectedProductIds.size === 0) {
        showNotification('Giỏ hàng của bạn đang trống.', 'error');
        return;
    }

    const addressPayload = getSelectedAddress();
    if (addressPayload?.error) {
        showNotification(addressPayload.error, 'error');
        return;
    }

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.disabled = true;
        checkoutBtn.textContent = 'Đang xử lý...';
    }

    try {
        const payload = {
            ...addressPayload,
            payment_method: getSelectedPaymentMethod(),
            promotion_id: promotionState.promotionId || null,
            selected_product_ids: getSelectedProductIds(),
        };

        const response = await checkoutOrder(payload);
        const data = response.data || response;

        showNotification('Đặt hàng thành công!');
        resetCheckoutUI();
        await loadCart({ showStatus: true });

        openOrderSuccessModal(data);

    } catch (error) {
        const message = error?.data?.errors || error?.data?.message || error?.message || 'Không thể đặt hàng.';
        showNotification(message, 'error');
    } finally {
        if (checkoutBtn) {
            checkoutBtn.disabled = false;
            checkoutBtn.textContent = 'Tiến hành thanh toán';
        }
    }
}