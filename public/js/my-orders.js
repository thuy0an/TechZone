/**
 * TechZone – My Orders Page JS
 * Đã gộp toàn bộ logic Hủy đơn (AJAX) vào chung để dễ quản lý và không cần reload trang.
 */
document.addEventListener('DOMContentLoaded', () => {
    const ITEMS_PER_PAGE = 10;
    let currentPage = 1;
    let currentOrders = [];
    let cancelOrderId = null; // Biến lưu ID đơn đang muốn hủy

    const container = document.getElementById('orders-container');
    const paginationContainer = document.getElementById('pagination-container');
    const btnFilter = document.getElementById('btn-filter');
    const metaText = document.getElementById('orders-meta');

    // =========================================================================
    // 1. CÁC HÀM TIỆN ÍCH (HELPERS)
    // =========================================================================
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
            'cancelled': { text: 'Đã hủy', class: 'status-cancelled' }
        };
        return map[status] || { text: status, class: 'status-default' };
    };

    function escapeHtmlAttr(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    // =========================================================================
    // 2. LOGIC HỦY ĐƠN HÀNG (CANCEL ORDER)
    // =========================================================================

    // Tạo và chèn Modal Hủy Đơn vào HTML (Chỉ chạy 1 lần)
    function injectCancelModal() {
        if (document.getElementById('cancel-order-modal')) return;

        const modal = document.createElement('div');
        modal.id = 'cancel-order-modal';
        modal.className = 'cancel-modal-overlay';
        modal.innerHTML = `
            <div class="cancel-modal-box">
                <div class="cancel-modal-icon">⚠️</div>
                <h3 id="cancel-modal-title">Hủy đơn hàng</h3>
                <p id="cancel-modal-desc">
                    Bạn có chắc muốn hủy đơn <strong id="cancel-modal-code"></strong>?<br>
                    Sau khi hủy, đơn hàng sẽ không thể khôi phục.
                </p>

                <div class="cancel-reason-group" style="margin: 15px 0; text-align: left;">
                    <label for="cancel-reason-select" style="font-size: 0.85rem; font-weight: 600; display: block; margin-bottom: 5px;">Lý do hủy đơn <span style="color: red;">*</span></label>
                    <select id="cancel-reason-select" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #d1d5db;">
                        <option value="">-- Chọn lý do --</option>
                        <option value="Thay đổi ý định mua">Thay đổi ý định mua</option>
                        <option value="Thời gian giao hàng quá lâu">Thời gian giao hàng quá lâu</option>
                        <option value="Tìm thấy giá rẻ hơn ở nơi khác">Tìm thấy giá rẻ hơn ở nơi khác</option>
                        <option value="Đặt nhầm sản phẩm">Đặt nhầm sản phẩm</option>
                        <option value="Lý do khác">Lý do khác</option>
                    </select>
                </div>

                <div class="cancel-modal-actions" style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                    <button class="btn" id="btn-cancel-modal-close">Không, Quay lại</button>
                    <button class="btn btn-cancel-order"  id="btn-cancel-modal-confirm">Đồng ý Hủy</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Nút Đóng modal
        document.getElementById('btn-cancel-modal-close').addEventListener('click', () => {
            document.getElementById('cancel-order-modal').style.display = 'none';
        });

        // Nút Xác nhận hủy
        document.getElementById('btn-cancel-modal-confirm').addEventListener('click', confirmCancelOrder);
    }

    // Gắn hàm mở Modal vào window để gọi được từ inline HTML (onclick)
    window.openCancelModal = function (orderId, orderCode) {
        cancelOrderId = orderId;
        document.getElementById('cancel-modal-code').textContent = orderCode;
        document.getElementById('cancel-reason-select').value = '';
        document.getElementById('cancel-order-modal').style.display = 'flex';
    };

    // Gọi API để hủy đơn
    async function confirmCancelOrder() {
        if (!cancelOrderId) return;

        const reason = document.getElementById('cancel-reason-select').value;
        console.log(reason)
        if (!reason) {
            showNotification('Vui lòng chọn lý do hủy đơn!', 'error');
            return;
        }

        const btn = document.getElementById('btn-cancel-modal-confirm');
        btn.disabled = true;
        btn.textContent = 'Đang xử lý...';

        try {
            await apiRequest(`/storefront/orders/${cancelOrderId}/cancel`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ cancel_reason: reason })
            });

            document.getElementById('cancel-order-modal').style.display = 'none';
            showNotification('Hủy đơn hàng thành công', 'success');

            // Cập nhật lại danh sách đơn hàng
            fetchOrders();

        } catch (error) {
            showNotification(error.message || 'Lỗi khi hủy đơn. Vui lòng thử lại.', 'error');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Đồng ý Hủy';
            cancelOrderId = null;
        }
    }

    // =========================================================================
    // 3. LOGIC LẤY & HIỂN THỊ DANH SÁCH ĐƠN HÀNG
    // =========================================================================
    async function fetchOrders() {
        container.innerHTML = '<div class="loading text-center">Đang tải dữ liệu đơn hàng...</div>';

        try {
            const params = new URLSearchParams({
                page: currentPage,
                per_page: ITEMS_PER_PAGE
            });

            // Gắn params lọc (nếu có)
            const code = document.getElementById('filter-code')?.value.trim();
            const status = document.getElementById('filter-status')?.value;
            const startDate = document.getElementById('filter-start-date')?.value;
            const endDate = document.getElementById('filter-end-date')?.value;

            if (code) params.append('order_code', code);
            if (status) params.append('status', status);
            if (startDate) params.append('start_date', startDate);
            if (endDate) params.append('end_date', endDate);

            const data = await apiRequest(`/storefront/orders?${params.toString()}`);

            if (data.data && data.data.length > 0) {
                currentOrders = data.data;
                metaText.textContent = `Hiển thị ${data.data.length} đơn hàng`;
                renderOrders(currentOrders);
                renderPagination(data.pagination);
            } else {
                container.innerHTML = '<div class="text-center" style="padding: 40px; color: var(--text-light);">Không tìm thấy đơn hàng nào.</div>';
                paginationContainer.classList.add('is-hidden');
                metaText.textContent = '0 đơn hàng';
            }
        } catch (error) {
            console.error('Lỗi khi tải đơn hàng:', error);
            container.innerHTML = '<div class="text-center" style="padding: 40px; color: red;">Không thể tải danh sách đơn hàng. Vui lòng thử lại sau.</div>';
        }
    }

    function renderOrders(orders) {
        container.innerHTML = '';

        orders.forEach(order => {
            const statusUI = getStatusUI(order.status);

            // Nút hủy chỉ hiện khi trạng thái là "new"
            const cancelBtnHtml = order.status === 'new' ? `
                <button class="btn-cancel-order" onclick="openCancelModal(${order.id}, '${escapeHtmlAttr(order.order_code)}')">
                    ✕ Hủy đơn
                </button>
            ` : '';

            // Render các sản phẩm trong đơn
            const itemsHtml = order.details.map(item => `
                <div class="order-item-detail" style="display: flex; gap: 15px; margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                    <img src="${item.product.image || 'img/no-image.png'}" alt="${item.product.name}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                    <div style="flex: 1;">
                        <h4 style="margin: 0; font-size: 0.95rem;">${item.product.name}</h4>
                        <p style="margin: 5px 0 0; color: var(--text-light); font-size: 0.85rem;">Phân loại: ${item.product.unit || 'Mặc định'} x ${item.quantity}</p>
                    </div>
                    <div style="text-align: right; font-weight: 600;">
                        ${formatCurrency(item.unit_price)}
                    </div>
                </div>
            `).join('');

            const orderCard = document.createElement('div');
            orderCard.className = 'order-card';
            orderCard.style.cssText = 'border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; margin-bottom: 20px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.04);';
            orderCard.innerHTML = `
                <div class="order-card-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                        <strong style="font-size: 1.1rem; color: var(--primary-color);">#${order.order_code}</strong>
                        <span style="color: var(--text-light); font-size: 0.85rem; margin-left: 10px;">Ngày đặt: ${formatDate(order.order_date)}</span>
                    </div>
                    <span class="badge ${statusUI.class}" style="padding: 6px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600;">${statusUI.text}</span>
                </div>
                
                <div class="order-card-body">
                    ${itemsHtml}
                </div>

                <div class="order-card-footer" style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; padding-top: 15px; border-top: 1px dashed var(--border-color);">
                    <div style="font-size: 0.9rem; color: var(--text-light);">
                        Thanh toán: <strong>${order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : order.payment_method}</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="font-size: 1.1rem;">
                            Tổng tiền: <strong style="color: var(--secondary-color); font-size: 1.2rem;">${formatCurrency(order.total_amount)}</strong>
                        </div>
                        ${cancelBtnHtml}
                    </div>
                </div>
            `;
            container.appendChild(orderCard);
        });
    }

    function renderPagination(pagination) {
        if (!pagination || pagination.last_page <= 1) {
            paginationContainer.classList.add('is-hidden');
            return;
        }

        paginationContainer.classList.remove('is-hidden');
        paginationContainer.innerHTML = '';

        // Nút Prev
        const prevBtn = document.createElement('button');
        prevBtn.className = 'page-btn';
        prevBtn.innerHTML = '&laquo;';
        prevBtn.disabled = pagination.current_page === 1;
        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                fetchOrders();
            }
        };
        paginationContainer.appendChild(prevBtn);

        // Các nút số trang
        for (let i = 1; i <= pagination.last_page; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.className = `page-btn ${i === pagination.current_page ? 'active' : ''}`;
            pageBtn.textContent = i;
            pageBtn.onclick = () => {
                if (currentPage !== i) {
                    currentPage = i;
                    fetchOrders();
                }
            };
            paginationContainer.appendChild(pageBtn);
        }

        // Nút Next
        const nextBtn = document.createElement('button');
        nextBtn.className = 'page-btn';
        nextBtn.innerHTML = '&raquo;';
        nextBtn.disabled = pagination.current_page === pagination.last_page;
        nextBtn.onclick = () => {
            if (currentPage < pagination.last_page) {
                currentPage++;
                fetchOrders();
            }
        };
        paginationContainer.appendChild(nextBtn);
    }

    // =========================================================================
    // 4. SỰ KIỆN LỌC & KHỞI CHẠY (INIT)
    // =========================================================================
    if (btnFilter) {
        btnFilter.addEventListener('click', () => {
            currentPage = 1;
            fetchOrders();
        });
    }

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
            el.addEventListener('change', () => {
                el.classList.remove('input-invalid');
                const errEl = document.getElementById(errId);
                if (errEl) errEl.textContent = '';
            });
        });
    }

    // Khởi chạy các hàm cần thiết khi load trang
    ensureFilterErrorSpans();
    injectCancelModal();
    fetchOrders();
});