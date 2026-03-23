
let state = {
    currentPage: 1,
    loading: false
};

document.addEventListener('DOMContentLoaded', () => {
    fetchOrders(state.currentPage);
});

async function fetchOrders(page) {
    const container = document.getElementById('order-container');
    state.loading = true;

    try {
        const res = await apiRequest(`/storefront/orders?page=${page}`);
        const { data: orders, current_page, last_page } = res.data;

        if (!orders || orders.length === 0) {
            container.innerHTML = `<div class="text-center p-50"><h4>Bạn chưa có đơn hàng nào</h4></div>`;
            return;
        }

        renderOrders(orders);
        renderPagination(current_page, last_page);
    } catch (error) {
        container.innerHTML = `<p class="text-danger">Lỗi: ${error.message}</p>`;
    } finally {
        state.loading = false;
    }
}

function renderOrders(orders) {
    const container = document.getElementById('order-container');
    container.innerHTML = orders.map(order => `
        <div class="order-card">
            <div class="order-header">
                <div>
                    <strong>Mã: <span class="text-primary">${order.order_code}</span></strong>
                    <div class="text-sm text-light">${new Date(order.created_at).toLocaleString('vi-VN')}</div>
                </div>
                <span class="badge ${getStatusBadge(order.status)}">${order.status}</span>
            </div>
            <div class="order-body">
                <p><strong>Người nhận:</strong> ${order.receiver_name} (${order.receiver_phone})</p>
                <p class="text-primary" style="font-size: 1.1rem; font-weight: bold;">
                    Tổng tiền: ${formatVND(order.total_amount)}
                </p>
            </div>
            <div class="order-footer mt-10">
                <button onclick="goToDetail(${order.id})" class="btn btn-primary btn-sm">Xem chi tiết</button>
            </div>
        </div>
    `).join('');
}

function renderPagination(current, last) {
    const nav = document.getElementById('pagination-root');
    nav.innerHTML = `
        <button class="btn-page" ${current === 1 ? 'disabled' : ''} onclick="changePage(${current - 1})">Trang trước</button>
        <span class="page-info">Trang ${current} / ${last}</span>
        <button class="btn-page" ${current === last ? 'disabled' : ''} onclick="changePage(${current + 1})">Trang sau</button>
    `;
}

function changePage(page) {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    fetchOrders(page);
}

function goToDetail(id) {
    window.location.href = `/thank-you.html?order_id=${id}`;
}

function getStatusBadge(status) {
    const map = { 'new': 'badge-info', 'completed': 'badge-success', 'canceled': 'badge-danger' };
    return map[status] || 'badge-warning';
}

function formatVND(val) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
}