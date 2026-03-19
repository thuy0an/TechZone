

// Khởi tạo giao diện chung của nhóm
initAdminLayout({ title: 'Khách hàng', icon: '👤', activePage: 'users' });

let currentPage = 1;
let searchTimeout = null;
let usersData = [];

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();

    document.getElementById('search-input').addEventListener('input', () => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            currentPage = 1;
            loadUsers();
        }, 400);
    });
});

async function loadUsers() {
    const keyword = document.getElementById('search-input').value.trim();
    const params = new URLSearchParams({ 
        page: currentPage, 
        limit: 10,
        keyword: keyword 
    });

    const tbody = document.getElementById('user-table-body');
    tbody.innerHTML = '<tr><td colspan="6" class="table-loading">⏳ Đang tải dữ liệu...</td></tr>';

    try {
        const res = await adminRequest(`/users?${params}`);
        usersData = res.data;
        renderTable(usersData);
        
        renderPagination({
            meta: res, 
            currentPage: currentPage,
            onPageChange: (p) => { 
                currentPage = p; 
                loadUsers(); 
            }
        });
    } catch (err) {
        tbody.innerHTML = `<tr><td colspan="6" class="table-loading" style="color:var(--admin-danger)">⚠️ Lỗi kết nối server</td></tr>`;
    }
}

function renderTable(items) {
    const tbody = document.getElementById('user-table-body');
    if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="table-loading">Không tìm thấy khách hàng nào.</td></tr>';
        return;
    }

    tbody.innerHTML = items.map(u => `
        <tr>
            <td class="td-id">#${u.id}</td>
            <td class="td-name"><strong>${escHtml(u.name)}</strong></td>
            <td>${u.email}</td>
            <td>${u.phone}</td>
            <td>
                <span class="badge ${u.is_locked ? 'danger' : 'success'}">
                    ${u.is_locked ? '⛔ Đã khóa' : '✅ Hoạt động'}
                </span>
            </td>
            <td>
                <div class="td-actions" style="justify-content: center;">
                    <button class="${u.is_locked ? 'btn-view' : 'btn-delete'}" 
                            onclick="handleToggleLock(${u.id}, ${u.is_locked})">
                        ${u.is_locked ? '🔓 Mở khóa' : '🔒 Khóa'}
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

async function handleToggleLock(id, isLocked) {
    const msg = isLocked ? "Bạn muốn mở khóa tài khoản này?" : "Bạn có chắc chắn muốn khóa tài khoản này?";
    if (!confirm(msg)) return;

    try {
        await adminRequest(`/users/${id}/lock`, { method: 'PUT' });
        showAdminToast('Cập nhật trạng thái thành công ✅');
        loadUsers();
    } catch (err) {
        showAdminToast('Không thể cập nhật trạng thái', 'error');
    }
}

function openAddModal() {
    document.getElementById('user-form').reset();
    document.getElementById('form-modal').classList.add('show');
}

function closeFormModal() {
    document.getElementById('form-modal').classList.remove('show');
}

async function saveUser() {
    const saveBtn = document.getElementById('save-btn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Đang xử lý...';

    const payload = {
        name: document.getElementById('u-name').value,
        email: document.getElementById('u-email').value,
        phone: document.getElementById('u-phone').value,
        address: document.getElementById('u-address').value
    };

    try {
        await adminRequest('/users', {
            method: 'POST',
            body: JSON.stringify(payload)
        });
        showAdminToast('Thêm khách hàng thành công ✅');
        closeFormModal();
        loadUsers();
    } catch (err) {
        const errorMsg = err.data?.message || 'Email đã tồn tại hoặc dữ liệu không hợp lệ';
        alert(errorMsg);
    } finally {
        saveBtn.disabled = false;
        saveBtn.textContent = 'Lưu thông tin';
    }
}