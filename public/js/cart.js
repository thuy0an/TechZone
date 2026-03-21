let GHN_TOKEN = '';
let GHN_API_URL = '';

document.addEventListener('DOMContentLoaded', async () => {
    initStorefrontLayout({ activePage: 'cart' });

    if (typeof isLoggedIn === 'function' && !isLoggedIn()) {
        try {
            const redirectUrl = window.location.pathname + window.location.search + window.location.hash;
            sessionStorage.setItem('post_login_redirect', redirectUrl);
        } catch (e) {
            // Ignore storage errors and proceed with redirect.
        }
        window.location.href = '/login.html';
        return;
    }

    const checkoutBtn = document.getElementById('checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            showNotification('Chuc nang thanh toan dang duoc phat trien.', 'error');
        });
    }

    loadCart({ showStatus: true });
    loadUserAddresses();

    await loadAppConfig();

    if (GHN_TOKEN) {
        loadProvinces();
    }
});

async function loadAppConfig() {
    try {
        const result = await apiRequest('/public-config')

        if (result.data) {
            GHN_TOKEN = result.data.ghn_token;
            GHN_API_URL = result.data.ghn_api_url;
        }
    } catch (error) {
        console.error('Không thể tải cấu hình hệ thống:', error);
    }
}

async function loadCart({ showStatus = false } = {}) {
    const status = document.getElementById('cart-status');
    const itemsContainer = document.getElementById('cart-items');

    if (showStatus && status) {
        status.textContent = 'Dang tai gio hang...';
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
                <h3>Gio hang dang trong</h3>
                <p>Hay them san pham de bat dau mua sam.</p>
                <a href="/products.html" class="btn btn-primary">Kham pha san pham</a>
            </div>
        `;
        updateTotals(0, subtotalElement, totalElement);
        return;
    }

    let subtotal = 0;

    itemsContainer.innerHTML = items.map(item => {
        const product = item.product || {};
        const currentPrice = Number(item.current_price ?? item.price_at_addition ?? product.selling_price ?? 0);
        const savedPrice = Number(item.price_at_addition ?? 0);
        const priceChanged = Boolean(item.is_price_changed);
        const oldPrice = Number(item.old_price ?? savedPrice);
        const quantity = Number(item.quantity || 0);
        const lineTotal = currentPrice * quantity;
        subtotal += lineTotal;

        return `
            <article class="cart-item" data-product-id="${product.id}" data-unit-price="${currentPrice}" data-quantity="${quantity}">
                <div class="cart-item-media">
                    <img src="${resolveImageUrl(product.image)}" alt="${escapeHtml(product.name || 'San pham')}" onerror="this.src='https://placehold.co/120x120?text=No+Image'">
                </div>
                <div class="cart-item-info">
                    <h4>${escapeHtml(product.name || 'San pham')}</h4>
                    <p class="cart-item-meta cart-item-price">
                        ${priceChanged ? `<span class="cart-item-price-old">${formatPrice(oldPrice)}</span>` : ''}
                        <span class="cart-item-price-current">${formatPrice(currentPrice)}</span>
                    </p>
                    <p class="cart-item-meta">Ton kho: ${product.stock_quantity ?? 0}</p>
                </div>
                <div class="cart-item-qty">
                    <button class="qty-btn" type="button" onclick="changeQuantity(${product.id}, -1)">-</button>
                    <span class="cart-item-qty-value">${quantity}</span>
                    <button class="qty-btn" type="button" onclick="changeQuantity(${product.id}, 1)">+</button>
                </div>
                <div class="cart-item-total">
                    <span class="cart-item-line-total">${formatPrice(lineTotal)}</span>
                    <button class="btn btn-link" type="button" onclick="removeItem(${product.id})">Xoa</button>
                </div>
            </article>
        `;
    }).join('');

    updateTotals(subtotal, subtotalElement, totalElement);
}

async function changeQuantity(productId, delta) {
    const itemRow = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    const qtyElement = itemRow?.querySelector('.cart-item-qty-value');
    const lineTotalElement = itemRow?.querySelector('.cart-item-line-total');
    const unitPrice = Number(itemRow?.dataset?.unitPrice || 0);
    const currentQty = Number(itemRow?.dataset?.quantity || 0);
    const nextQty = currentQty + delta;

    if (itemRow && nextQty <= 0) {
        itemRow.remove();
        updateTotalsFromDom();
    }

    if (itemRow && nextQty > 0) {
        itemRow.dataset.quantity = String(nextQty);
        if (qtyElement) qtyElement.textContent = String(nextQty);
        if (lineTotalElement) lineTotalElement.textContent = formatPrice(unitPrice * nextQty);
        updateTotalsFromDom();
    }

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

    try {
        await updateCartItem(productId, 0);
        await updateCartCount();
        showNotification('Da xoa san pham khoi gio hang.');
    } catch (error) {
        const message = error?.data?.message || error?.message || 'Khong the xoa san pham.';
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
        const unitPrice = Number(item.dataset.unitPrice || 0);
        const quantity = Number(item.dataset.quantity || 0);
        subtotal += unitPrice * quantity;
    });

    updateTotals(subtotal, subtotalElement, totalElement);

    const itemsContainer = document.getElementById('cart-items');
    if (items.length === 0 && itemsContainer) {
        itemsContainer.innerHTML = `
            <div class="cart-empty">
                <div class="cart-empty-icon">🛒</div>
                <h3>Gio hang dang trong</h3>
                <p>Hay them san pham de bat dau mua sam.</p>
                <a href="/products.html" class="btn btn-primary">Kham pha san pham</a>
            </div>
        `;
    }
}

function updateTotals(subtotal, subtotalElement, totalElement) {
    const formatted = formatPrice(subtotal);
    if (subtotalElement) subtotalElement.textContent = formatted;
    if (totalElement) totalElement.textContent = formatted;
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
    return `/storage/${image}`;
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

async function loadUserAddresses() {
    try {
        const response = await apiRequest('/storefront/addresses', 'GET');

        const addresses = response.data;
        const select = document.getElementById('existing-address-block');
        select.innerHTML = '';

        if (!addresses || addresses.length === 0) {
            document.getElementById('opt_new').click();
            document.getElementById('opt_existing').disabled = true;
            select.innerHTML = '<option value="">Bạn chưa có địa chỉ nào được lưu</option>';
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
                                ${addr.address}, ${addr.ward_name}, ${addr.district_name}, ${addr.province_name}
                            </p>
                        </label>
                    </div>
                `;

                select.insertAdjacentHTML('beforeend', cardHtml);
            });

            document.getElementById('opt_existing').disabled = false;
            document.getElementById('opt_existing').click();
        }
    } catch (error) {
        console.error("Lỗi khi tải địa chỉ:", error);
        document.getElementById('existing-address-select').innerHTML = '<option value="">Không thể tải địa chỉ</option>';
    }
}

async function loadProvinces() {
    try {
        const response = await fetch(`${GHN_API_URL}/province`, {
            headers: { 'Token': GHN_TOKEN }
        });
        const data = await response.json();

        const citySelect = document.getElementById('new_city');

        data.data.forEach(province => {
            if (province.ProvinceID < 270) {
                let option = document.createElement('option');
                option.value = province.ProvinceID;
                option.text = province.ProvinceName;
                citySelect.appendChild(option);
            }
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
        const response = await fetch(`${GHN_API_URL}/district?province_id=${provinceId}`, {
            headers: { 'Token': GHN_TOKEN }
        });
        const data = await response.json();

        data.data.forEach(district => {
            let option = document.createElement('option');
            option.value = district.DistrictID;
            option.text = district.DistrictName;
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
        const response = await fetch(`${GHN_API_URL}/ward?district_id=${districtId}`, {
            headers: { 'Token': GHN_TOKEN }
        });
        const data = await response.json();

        data.data.forEach(ward => {
            let option = document.createElement('option');
            option.value = ward.WardCode;
            option.text = ward.WardName;
            wardSelect.appendChild(option);
        });
        wardSelect.disabled = false;
    } catch (error) {
        console.error("Lỗi khi tải danh sách Phường/Xã:", error);
    }
}
