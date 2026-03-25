document.addEventListener('DOMContentLoaded', () => {
    // Cấu hình
    const ITEMS_PER_PAGE = 10;
    let currentPage = 1;
    let currentOrders = [];
    let paginationData = null;

    const container = document.getElementById('orders-container');
    const paginationContainer = document.getElementById('pagination-container');
    const btnFilter = document.getElementById('btn-filter');
    const metaText = document.getElementById('orders-meta');

    const formatCurrency = (amount) => {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    };

    const formatDate = (dateString) => {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
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
        return map[status] || { text: status, class: 'status-new' };
    };

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

        const params = new URLSearchParams({
            page: currentPage,
            per_page: ITEMS_PER_PAGE
        });

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
            metaText.textContent = `0 đơn hàng`;
            paginationContainer.classList.add('is-hidden');
            return;
        }

        metaText.textContent = `Trang ${paginationData.current_page}/${paginationData.last_page} • Tổng ${paginationData.total} đơn hàng`;

        let html = '';
        currentOrders.forEach(order => {
            const statusUI = getStatusUI(order.status);

            let detailsHtml = order.details.map(detail => `
                <div class="order-item-row" style="border: none; padding: 10px 0; border-bottom: 1px dashed #e2e8f0; border-radius: 0;">
                    <img src="${detail.product?.image ? 'storage/' + detail.product.image : 'https://via.placeholder.com/64'}" alt="${detail.product?.name || 'Sản phẩm'}">
                    <div>
                        <div style="font-weight: 600; color: #1f2937;">${detail.product?.name || 'Sản phẩm không xác định'}</div>
                        <div class="order-item-meta">SL: x${detail.quantity}</div>
                    </div>
                    <div class="order-item-total">${formatCurrency(detail.unit_price)}</div>
                </div>
            `).join('');

            html += `
                <div class="order-history-card">
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
                            <span style="color: var(--text-light); font-size: 0.9rem;">Tổng tiền:</span>
                            <div class="order-total-price">${formatCurrency(order.total_amount)}</div>
                        </div>
                        <a href="order-summary.html?id=${order.id}&myOrder=true" class="btn btn-secondary" style="padding: 8px 20px;">Xem chi tiết</a>
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
        renderPagination();
    };

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

        const pageBtns = paginationContainer.querySelectorAll('.page-btn:not(.disabled)');
        pageBtns.forEach(btn => {
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

    btnFilter.addEventListener('click', () => {
        currentPage = 1;
        fetchOrders();
    });

    fetchOrders();
});