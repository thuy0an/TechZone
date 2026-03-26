/**
 * TechZone – My Orders Page JS
 * Bổ sung: nút hủy đơn (trạng thái "new") + validation form lọc
 */
document.addEventListener('DOMContentLoaded', () => {
    const ITEMS_PER_PAGE = 10;
    let currentPage = 1;
    let currentOrders = [];
    let paginationData = null;

    const container = document.getElementById('orders-container');
    const paginationContainer = document.getElementById('pagination-container');
    const btnFilter = document.getElementById('btn-filter');
    const metaText = document.getElementById('orders-meta');

    // ── Helpers ────────────────────────────────────────────────────────────────

    const formatCurrency = (amount) =>
        new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN') + ' '
            + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
    };

    const getStatusUI = (status) => {
        const map = {
            'new': { text: 'Chờ xác nhận', class: 'status-new' },
            'confirmed': { text: 'Đã xác nhận', class: 'status-confirmed' },
            'shipping': { text: 'Đang giao hàng', class: 'status-shipping' },
            'delivered': { text: 'Đã giao thành công', class: 'status-completed' },
            'completed': { text: 'Đã giao thành công', class: 'status-completed' },
            'failed': { text: 'Giao thất bại', class: 'status-failed' },
            'cancelled': { text: 'Đã hủy', class: 'status-cancelled' },
        };
        return map[status] || { text: status, class: 'status-new' };
    };

    // ── Filter validation ──────────────────────────────────────────────────────

    function validateFilters() {
        const startEl = document.getElementById('filter-start-date');
        const endEl = document.getElementById('filter-end-date');
        const startErrEl = document.getElementById('filter-start-date-error');
        const endErrEl = document.getElementById('filter-end-date-error');

        // Clear errors
        if (startErrEl) startErrEl.textContent = '';
        if (endErrEl) endErrEl.textContent = '';
        startEl?.classList.remove('input-invalid');
        endEl?.classList.remove('input-invalid');

        const startVal = startEl?.value;
        const endVal = endEl?.value;

        if (startVal && endVal && new Date(startVal) > new Date(endVal)) {
            if (endErrEl) endErrEl.textContent = 'Ngày kết thúc phải sau ngày bắt đầu.';
            endEl?.classList.add('input-invalid');
            endEl?.focus();
            return false;
        }

        return true;
    }

    // ── Fetch & Render ─────────────────────────────────────────────────────────

    const fetchOrders = async () => {
        const token = localStorage.getItem('storefront_token');

        if (!token) {
            container.innerHTML = `<div class="empty-state">Vui lòng <a href="login.html" class="text-primary">đăng nhập</a> để xem lịch sử đơn hàng.</div>`;
            return;
        }

        container.innerHTML = '<div class="loading text-center">Đang tải dữ liệu đơn hàng...</div>';

        const codeFilter = document.getElementById('filter-code').value.trim();
        const startDateFilter = document.getElementById('filter-start-date').value;
        const endDateFilter = document.getElementById('filter-end-date').value;
        const statusFilter = document.getElementById('filter-status').value;

        const params = new URLSearchParams({ page: currentPage, per_page: ITEMS_PER_PAGE });

        if (codeFilter) params.append('code', codeFilter);
        if (startDateFilter) params.append('start_date', startDateFilter);
        if (endDateFilter) params.append('end_date', endDateFilter);
        if (statusFilter) params.append('status', statusFilter);

        try {
            const result = await apiRequest(`/storefront/orders?${params.toString()}`);

            if (result.success) {
                currentOrders = result.data;
                paginationData = result.pagination;
                renderPage();
            } else {
                container.innerHTML = `<div class="empty-state error-text">${result.message || 'Không thể lấy dữ liệu đơn hàng.'}</div>`;
            }
        } catch (error) {
            console.error('Lỗi khi fetch orders:', error);
            container.innerHTML = `<div class="empty-state error-text">Lỗi kết nối đến máy chủ.</div>`;
        }
    };

    const renderPage = () => {
        if (!currentOrders || currentOrders.length === 0) {
            container.innerHTML = `<div class="empty-state">Không tìm thấy đơn hàng nào phù hợp.</div>`;
            if (metaText) metaText.textContent = `0 đơn hàng`;
            paginationContainer.classList.add('is-hidden');
            return;
        }

        if (metaText) {
            metaText.textContent = `Trang ${paginationData.current_page}/${paginationData.last_page} • Tổng ${paginationData.total} đơn hàng`;
        }

        let html = '';
        currentOrders.forEach(order => {
            const statusUI = getStatusUI(order.status);
            const canCancel = order.status === 'new';

            const detailsHtml = order.details.map(detail => `
                <div class="order-item-row" style="border:none; padding:10px 0; border-bottom:1px dashed #e2e8f0; border-radius:0;">
                    <img src="${detail.product?.image ? 'storage/' + detail.product.image : 'https://via.placeholder.com/64'}"
                         alt="${detail.product?.name || 'Sản phẩm'}">
                    <div>
                        <div style="font-weight:600; color:#1f2937;">${detail.product?.name || 'Sản phẩm không xác định'}</div>
                        <div class="order-item-meta">SL: x${detail.quantity}</div>
                    </div>
                    <div class="order-item-total">${formatCurrency(detail.unit_price)}</div>
                </div>
            `).join('');

            // Nút hành động: hủy đơn (chỉ hiện khi status = "new") + xem chi tiết
            const cancelBtn = canCancel ? `
                <button
                    class="btn-cancel-order"
                    onclick="openCancelOrderModal(${order.id}, '${escapeHtmlAttr(order.order_code)}', onCancelSuccess)"
                    title="Hủy đơn hàng này"
                >
                    ✕ Hủy đơn
                </button>
            ` : '';

            html += `
                <div class="order-history-card" id="order-card-${order.id}">
                    <div class="order-history-header">
                        <div>
                            <span class="order-code">${order.order_code}</span>
                            <span class="order-date">Ngày đặt: ${formatDate(order.order_date)}</span>
                        </div>
                        <div class="order-status ${statusUI.class}">${statusUI.text}</div>
                    </div>

                    <div class="order-history-body">
                        ${detailsHtml}
                    </div>

                    <div class="order-history-footer">
                        <div>
                            <span style="color:var(--text-light); font-size:0.9rem;">Tổng tiền:</span>
                            <div class="order-total-price">${formatCurrency(order.total_amount)}</div>
                        </div>
                        <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                            ${cancelBtn}
                            <a href="order-summary.html?id=${order.id}&myOrder=true"
                               class="btn btn-secondary"
                               style="padding:8px 20px;">
                               Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        renderPagination();
    };

    // ── Callback khi hủy thành công ────────────────────────────────────────────
    window.onCancelSuccess = function (res) {
        const orderId = res?.data?.order_id;
        if (!orderId) { fetchOrders(); return; }

        // Cập nhật UI ngay lập tức không cần reload toàn bộ trang
        const card = document.getElementById(`order-card-${orderId}`);
        if (card) {
            // Cập nhật badge trạng thái
            const badge = card.querySelector('.order-status');
            if (badge) {
                badge.className = 'order-status status-cancelled';
                badge.textContent = 'Đã hủy';
            }

            // Ẩn nút hủy
            const cancelBtn = card.querySelector('.btn-cancel-order');
            if (cancelBtn) cancelBtn.remove();

            // Thêm visual cue: mờ card đi nhẹ
            card.style.opacity = '0.75';
            card.style.transition = 'opacity 0.4s';
        }
    };

    // ── Pagination ─────────────────────────────────────────────────────────────
    const renderPagination = () => {
        if (!paginationData || paginationData.last_page <= 1) {
            paginationContainer.classList.add('is-hidden');
            return;
        }

        paginationContainer.classList.remove('is-hidden');
        const { current_page, last_page } = paginationData;
        let html = '';

        html += `<button class="page-btn ${current_page <= 1 ? 'disabled' : ''}" data-page="${current_page - 1}">&#8592;</button>`;
        for (let i = 1; i <= last_page; i++) {
            html += `<button class="page-btn ${current_page === i ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }
        html += `<button class="page-btn ${current_page >= last_page ? 'disabled' : ''}" data-page="${current_page + 1}">&#8594;</button>`;

        paginationContainer.innerHTML = html;

        paginationContainer.querySelectorAll('.page-btn:not(.disabled)').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const newPage = parseInt(e.target.getAttribute('data-page'));
                if (newPage && newPage !== currentPage) {
                    currentPage = newPage;
                    fetchOrders();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    };

    // ── Event: nút lọc ─────────────────────────────────────────────────────────
    btnFilter.addEventListener('click', () => {
        if (!validateFilters()) return;
        currentPage = 1;
        fetchOrders();
    });

    // ── Inject error span cho date filters nếu chưa có ────────────────────────
    function ensureFilterErrorSpans() {
        ['filter-start-date', 'filter-end-date'].forEach(id => {
            const el = document.getElementById(id);
            if (!el) return;
            const errId = `${id}-error`;
            if (!document.getElementById(errId)) {
                const span = document.createElement('div');
                span.id = errId;
                span.className = 'filter-field-error';
                span.style.cssText = 'font-size:0.78rem; color:#ef4444; margin-top:3px; min-height:16px;';
                el.parentNode.insertBefore(span, el.nextSibling);
            }
            // Clear khi user thay đổi
            el.addEventListener('change', () => {
                el.classList.remove('input-invalid');
                const errEl = document.getElementById(errId);
                if (errEl) errEl.textContent = '';
            });
        });
    }

    ensureFilterErrorSpans();

    // ── Khởi động ──────────────────────────────────────────────────────────────
    fetchOrders();
});

// ── Utility ────────────────────────────────────────────────────────────────────
function escapeHtmlAttr(str) {
    return String(str || '')
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}