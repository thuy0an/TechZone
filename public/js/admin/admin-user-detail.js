// Khởi tạo giao diện chung
initAdminLayout({ title: 'Khách hàng', icon: '👤', activePage: 'users' });

const urlParams = new URLSearchParams(window.location.search);
const targetUserId = urlParams.get('id');

let currentUser = null;
let currentOrderPage = 1;

if (!targetUserId) {
    alert('Không tìm thấy ID khách hàng!');
    window.location.href = 'users.html';
}

document.addEventListener('DOMContentLoaded', () => {
    loadUserInfo();
    loadUserAddresses();
    loadUserOrders();
});

async function loadUserInfo() {
    try {
        const res = await adminRequest(`/users/${targetUserId}`);
        currentUser = res.data;

        document.getElementById('header-user-name').textContent = `- ${currentUser.name}`;
        document.getElementById('ud-name').value = currentUser.name;
        document.getElementById('ud-email').value = currentUser.email;
        document.getElementById('ud-phone').value = currentUser.phone || '';

        const badge = document.getElementById('user-status-badge');
        const lockBtn = document.getElementById('btn-toggle-lock');
        if (currentUser.is_locked) {
            badge.className = 'badge danger';
            badge.textContent = '⛔ Đã bị khóa';
            lockBtn.className = 'btn btn-view';
            lockBtn.textContent = '🔓 Mở khóa tài khoản';
        } else {
            badge.className = 'badge success';
            badge.textContent = '✅ Hoạt động';
            lockBtn.className = 'btn btn-danger';
            lockBtn.textContent = '🔒 Khóa tài khoản';
        }
    } catch (error) {
        showAdminToast('Không thể tải thông tin khách hàng', 'error');
    }
}

async function updateUserInfo() {
    const btnSave = document.getElementById('btn-update-info');
    btnSave.disabled = true;
    btnSave.textContent = 'Đang lưu...';

    const payload = {
        name: document.getElementById('ud-name').value.trim(),
        phone: document.getElementById('ud-phone').value.trim()
    };

    try {
        await adminRequest(`/users/${targetUserId}`, {
            method: 'PUT',
            body: JSON.stringify(payload)
        });
        showAdminToast('Cập nhật thông tin thành công ✅');
        loadUserInfo(); // Tải lại header
    } catch (error) {
        showAdminToast(error.data?.message || 'Lỗi cập nhật', 'error');
    } finally {
        btnSave.disabled = false;
        btnSave.textContent = 'Lưu thông tin';
    }
}

async function toggleUserLock() {
    const msg = currentUser.is_locked ? "Bạn muốn mở khóa tài khoản này?" : "Bạn có chắc chắn muốn khóa tài khoản này?";
    if (!confirm(msg)) return;

    try {
        await adminRequest(`/users/${targetUserId}/lock`, { method: 'PUT' });
        showAdminToast('Cập nhật trạng thái thành công ✅');
        loadUserInfo();
    } catch (err) {
        showAdminToast('Không thể cập nhật trạng thái', 'error');
    }
}

async function loadUserAddresses() {
    const tbody = document.getElementById('address-table-body');
    try {
        const res = await adminRequest(`/users/${targetUserId}/addresses`);
        const addresses = res.data || [];

        if (addresses.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="table-empty">Khách hàng chưa có địa chỉ nào.</td></tr>';
            return;
        }

        tbody.innerHTML = addresses.map(addr => `
            <tr>
                <td style="font-weight: 600">${escHtml(addr.receiver_name)}</td>
                <td>${escHtml(addr.receiver_phone)}</td>
                <td>${escHtml(addr.address)}</td>
                <td>${escHtml(addr.address)}</td>
                <td>${addr.is_default ? '<span class="badge success" style="min-width:auto">Mặc định</span>' : ''}</td>
            </tr>
        `).join('');
    } catch (error) {
        tbody.innerHTML = '<tr><td colspan="5" class="table-loading" style="color:red">Lỗi tải địa chỉ</td></tr>';
    }
}

function getStatusConfig(status) {
    const map = {
        'new': { label: 'Mới', class: 'secondary' },
        'confirmed': { label: 'Đã xác nhận', class: 'info' },
        'shipping': { label: 'Đang giao', class: 'processing' },
        'delivered': { label: 'Đã giao hàng', class: 'warning' },
        'completed': { label: 'Hoàn thành', class: 'success' },
        'cancelled': { label: 'Đã hủy', class: 'danger' },
        'failed': { label: 'Thất bại', class: 'danger' }
    };
    return map[status] || { label: status, class: 'secondary' };
}

async function loadUserOrders() {
    const search = document.getElementById('filter-order-code').value.trim();
    const startDate = document.getElementById('filter-start').value;
    const endDate = document.getElementById('filter-end').value;
    const status = document.getElementById('filter-status').value;

    const params = new URLSearchParams({
        page: currentOrderPage,
        per_page: 5,
        user_id: targetUserId
    });

    if (search) params.append('search', search);
    if (status) params.append('status', status);
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);

    const tbody = document.getElementById('order-table-body');
    tbody.innerHTML = '<tr><td colspan="5" class="table-loading">⏳ Đang tải...</td></tr>';
    document.getElementById('pagination-bar').style.display = 'none';

    try {
        const res = await adminRequest(`/orders?${params}`);
        const items = res.data.data ?? res.data;

        if (!items || items.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="table-empty">Không tìm thấy đơn hàng nào.</td></tr>`;
            return;
        }

        tbody.innerHTML = items.map(order => {
            const statusCfg = getStatusConfig(order.status);
            return `
            <tr>
                <td style="font-weight: 600">#${order.order_code || order.id}</td>
                <td>${formatDate(order.created_at)}</td>
                <td style="font-weight: 700; color: var(--admin-accent);">${new Intl.NumberFormat('vi-VN').format(order.total_amount)}đ</td>
                <td>${order.payment_method === 'cash' ? 'Tiền mặt' : 'Online'}</td>
                <td><span class="badge ${statusCfg.class}" style="min-width:auto">${statusCfg.label}</span></td>
            </tr>`;
        }).join('');

        renderPagination({
            meta: res.pagination,
            currentPage: currentOrderPage,
            onPageChange: (p) => { currentOrderPage = p; loadUserOrders(); }
        });
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="5" class="table-loading" style="color:red">Lỗi tải dữ liệu đơn hàng</td></tr>`;
    }
}