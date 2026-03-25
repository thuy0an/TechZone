document.addEventListener('DOMContentLoaded', () => {
    // Cấu hình
    const ITEMS_PER_PAGE = 10;

    let allOrders = [];      // 
    let filteredOrders = [];
    let currentPage = 1;

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

        try {
            const result = await apiRequest(`/storefront/orders`)

            if (result.success) {
                allOrders = result.data;
                filteredOrders = [...allOrders];
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
        if (filteredOrders.length === 0) {
            container.innerHTML = `<div class="empty-state">Không tìm thấy đơn hàng nào phù hợp.</div>`;
            metaText.textContent = `0 đơn hàng`;
            paginationContainer.classList.add('is-hidden');
            return;
        }

        metaText.textContent = `Hiển thị ${filteredOrders.length} đơn hàng`;

        const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
        const endIndex = startIndex + ITEMS_PER_PAGE;
        const ordersToShow = filteredOrders.slice(startIndex, endIndex);

        let html = '';
        ordersToShow.forEach(order => {
            const statusUI = getStatusUI(order.status);

            let detailsHtml = order.details.map(detail => `
                <div class="order-item-row" style="border: none; padding: 10px 0; border-bottom: 1px dashed #e2e8f0; border-radius: 0;">
                    <img src="${detail.product.image ? 'storage/' + detail.product.image : 'https://via.placeholder.com/64'}" alt="${detail.product.name}">
                    <div>
                        <div style="font-weight: 600; color: #1f2937;">${detail.product.name}</div>
                        <div class="order-item-meta">Phân loại: ${detail.product.category_id} | SL: x${detail.quantity}</div>
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
        const totalPages = Math.ceil(filteredOrders.length / ITEMS_PER_PAGE);

        if (totalPages <= 1) {
            paginationContainer.classList.add('is-hidden');
            return;
        }

        paginationContainer.classList.remove('is-hidden');
        let html = '';

        html += `<button class="page-btn ${currentPage === 1 ? 'disabled' : ''}" data-page="${currentPage - 1}">&#8592;</button>`;

        for (let i = 1; i <= totalPages; i++) {
            html += `<button class="page-btn ${currentPage === i ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        html += `<button class="page-btn ${currentPage === totalPages ? 'disabled' : ''}" data-page="${currentPage + 1}">&#8594;</button>`;

        paginationContainer.innerHTML = html;

        const pageBtns = paginationContainer.querySelectorAll('.page-btn:not(.disabled)');
        pageBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const newPage = parseInt(e.target.getAttribute('data-page'));
                if (newPage && newPage !== currentPage) {
                    currentPage = newPage;
                    renderPage();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        });
    };

    btnFilter.addEventListener('click', () => {
        const codeFilter = document.getElementById('filter-code').value.trim().toLowerCase();
        const startDateFilter = document.getElementById('filter-start-date').value;
        const endDateFilter = document.getElementById('filter-end-date').value;
        const statusFilter = document.getElementById('filter-status').value;

        filteredOrders = allOrders.filter(order => {
            let isValid = true;

            if (codeFilter && !order.order_code.toLowerCase().includes(codeFilter)) {
                isValid = false;
            }
            if (statusFilter && order.status !== statusFilter) {
                isValid = false;
            }

            const orderDateOnly = order.order_date.split(' ')[0];
            if (startDateFilter && orderDateOnly < startDateFilter) {
                isValid = false;
            }
            if (endDateFilter && orderDateOnly > endDateFilter) {
                isValid = false;
            }

            return isValid;
        });

        currentPage = 1;
        renderPage();
    });

    fetchOrders();
});