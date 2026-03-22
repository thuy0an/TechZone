document.addEventListener('DOMContentLoaded', async () => {
    initStorefrontLayout({ activePage: 'orders' });

    if (typeof isLoggedIn === 'function' && !isLoggedIn()) {
        window.location.href = '/login.html';
        return;
    }

    const params = new URLSearchParams(window.location.search);
    const orderId = params.get('id');

    if (!orderId) {
        renderSummaryError('Không tìm thấy mã đơn hàng.');
        return;
    }

    try {
        const response = await getOrderSummary(orderId);
        const summary = response.data || response;
        renderSummary(summary);
    } catch (error) {
        const message = error?.data?.message || error?.message || 'Không thể tải đơn hàng.';
        renderSummaryError(message);
    }
});

function renderSummary(summary) {
    const status = document.getElementById('order-summary-status');
    const overview = document.getElementById('order-summary-overview');
    const paymentSection = document.getElementById('order-summary-payment');
    const itemsSection = document.getElementById('order-summary-items');

    if (status) {
        status.textContent = '';
        status.classList.remove('is-loading');
    }

    if (overview) overview.style.display = 'block';
    if (itemsSection) itemsSection.style.display = 'block';

    const code = summary.order_code || `#${summary.order_id}`;
    const createdAt = summary.created_at || '';

    const codeElement = document.getElementById('order-summary-code');
    if (codeElement) codeElement.textContent = `Mã đơn: ${code}`;

    const metaElement = document.getElementById('order-summary-meta');
    if (metaElement) {
        metaElement.innerHTML = `
            <span>Trạng thái: ${formatStatus(summary.status)}</span>
            <span>Thanh toán: ${formatPayment(summary.payment_method)}</span>
            ${createdAt ? `<span>Ngày đặt: ${createdAt}</span>` : ''}
        `;
    }

    const totalElement = document.getElementById('order-summary-total');
    if (totalElement) totalElement.textContent = formatPrice(summary.total_amount || 0);

    const infoElement = document.getElementById('order-summary-info');
    if (infoElement) {
        infoElement.innerHTML = `
            <div class="order-summary-line">
                <strong>Người nhận</strong>
                <div>${summary.receiver_name || ''}</div>
                <div>${summary.receiver_phone || ''}</div>
            </div>
            <div class="order-summary-line">
                <strong>Địa chỉ giao hàng</strong>
                <div>${summary.shipping_address || ''}</div>
            </div>
        `;
    }

    if (summary.bank_transfer_info) {
        if (paymentSection) paymentSection.style.display = 'block';
        const paymentInfo = document.getElementById('order-summary-payment-info');
        if (paymentInfo) {
            paymentInfo.innerHTML = `
                <div class="order-summary-line">
                    <strong>Ngân hàng</strong>
                    <div>${summary.bank_transfer_info.bank_name}</div>
                </div>
                <div class="order-summary-line">
                    <strong>STK</strong>
                    <div>${summary.bank_transfer_info.account_number}</div>
                </div>
                <div class="order-summary-line">
                    <strong>Chủ TK</strong>
                    <div>${summary.bank_transfer_info.account_owner}</div>
                </div>
                <div class="order-summary-line">
                    <strong>Nội dung</strong>
                    <div>${summary.bank_transfer_info.transfer_note}</div>
                </div>
            `;
        }
    }

    const itemsElement = document.getElementById('order-summary-items-list');
    if (itemsElement) {
        const items = summary.items || [];
        itemsElement.innerHTML = items.map(item => `
            <article class="order-item-row">
                <img src="${resolveImageUrl(item.product_image)}" alt="${escapeHtml(item.product_name || 'Sản phẩm')}">
                <div>
                    <strong>${escapeHtml(item.product_name || 'Sản phẩm')}</strong>
                    <div class="order-item-meta">Số lượng: ${item.quantity}</div>
                    <div class="order-item-meta">Đơn giá: ${formatPrice(item.unit_price)}</div>
                </div>
                <div class="order-item-total">${formatPrice(item.line_total)}</div>
            </article>
        `).join('');
    }
}

function renderSummaryError(message) {
    const status = document.getElementById('order-summary-status');
    if (status) {
        status.textContent = message;
        status.classList.remove('is-loading');
    }
}

function formatPrice(value) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        maximumFractionDigits: 0,
    }).format(value || 0);
}

function formatPayment(method) {
    if (method === 'bank_transfer') return 'Chuyển khoản ngân hàng';
    if (method === 'online') return 'Thanh toán online';
    return 'Thanh toán khi nhận hàng';
}

function formatStatus(status) {
    return status ? status.toUpperCase() : 'NEW';
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
