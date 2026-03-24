/**
 * TechZone Admin – Layout Module
 *
 * Tương tự BaseService/BaseController ở backend:
 *   – Inject sidebar, topbar, logout modal vào từng trang
 *   – Chứa các hàm lifecycle dùng chung (confirmLogout, doLogout)
 *
 * Sử dụng:
 *   initAdminLayout({ title: 'Tiêu đề', icon: '📂', activePage: 'categories' })
 */

// ============================================================
// Nav config – thêm trang mới ở đây là đủ
// ============================================================
const ADMIN_NAV = [
    { type: 'section', label: 'Tổng quan' },
    { type: 'item', page: 'dashboard', href: 'dashboard.html', icon: 'bx bx-grid-alt', label: 'Dashboard' },
    { type: 'item', page: 'reports', href: 'reports.html', icon: 'bx bx-line-chart', label: 'Báo cáo & Thống kê' },
    { type: 'section', label: 'Danh mục' },
    { type: 'item', page: 'categories', href: 'categories.html', icon: 'bx bx-folder', label: 'Loại sản phẩm' },
    { type: 'item', page: 'brands', href: 'brands.html', icon: 'bx bx-purchase-tag', label: 'Thương hiệu' },
    { type: 'section', label: 'Quản lý' },
    { type: 'item', page: 'products', href: 'products.html', icon: 'bx bx-package', label: 'Sản phẩm' },
    { type: 'item', page: 'orders', href: 'orders.html', icon: 'bx bx-cart', label: 'Đơn hàng' },
    { type: 'item', page: 'suppliers', href: 'suppliers.html', icon: 'bx bx-buildings', label: 'Nhà cung cấp' },
    { type: 'item', page: 'import-notes', href: 'import-notes.html', icon: 'bx bx-import', label: 'Phiếu nhập hàng' },
    { type: 'item', page: 'customers', href: 'users.html', icon: 'bx bx-group', label: 'Khách hàng' },
    { type: 'item', page: 'promotions', href: 'promotions.html', icon: 'bx bx-gift', label: 'Khuyến mãi' }
];

// ============================================================
// Main init – gọi 1 lần ở đầu mỗi trang
// ============================================================
/**
 * D – Dependency Inversion: authGuard và renderIdentity được inject từ ngoài.
 *     Mặc định dùng hàm từ admin-auth.js; test có thể truyền stub vào.
 */
function initAdminLayout({
    title = 'Admin',
    icon = '',
    activePage = '',
    authGuard = requireAdminAuth,
    renderIdentity = renderAdminIdentity,
} = {}) {
    _injectSidebar(activePage);
    _injectTopbar(title, icon);
    _injectLogoutModal();

    document.title = `${title} - TechZone Admin`;

    authGuard();
    renderIdentity();
}

// ============================================================
// Sidebar
// ============================================================
function _injectSidebar(activePage) {
    const root = document.getElementById('admin-sidebar-root');
    if (!root) return;

    if (!document.getElementById('boxicons-css')) {
        document.head.insertAdjacentHTML('beforeend', `<link id="boxicons-css" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">`);
    }

    const navHtml = ADMIN_NAV.map(item => {
        if (item.type === 'section') {
            return `<div class="sidebar-section-title">${item.label}</div>`;
        }
        const isActive = item.page === activePage ? 'active' : '';
        return `
            <a href="${item.href}" class="sidebar-nav-item ${isActive}" data-page="${item.page}">
                <i class="nav-icon ${item.icon}"></i> ${item.label}
            </a>`;
    }).join('');

    root.outerHTML = `
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <i class="sidebar-brand-icon bx bx-shield-quarter"></i>
            <div>
                <div class="sidebar-brand-text">TechZone</div>
                <div class="sidebar-brand-sub">Admin Panel</div>
            </div>
        </div>
        <nav class="sidebar-nav">
            ${navHtml}
        </nav>
        <div class="sidebar-footer">
            <div class="admin-user-info">
                <div class="admin-avatar">A</div>
                <div>
                    <div class="admin-user-name">Admin</div>
                    <div class="admin-user-email"></div>
                </div>
            </div>
            <button class="btn-logout" onclick="confirmLogout()">
                <i class="bx bx-log-out" style="font-size: 1.1rem; margin-right: 4px; vertical-align: middle;"></i> Đăng xuất
            </button>
        </div>
    </aside>`;
}

// ============================================================
// Topbar
// ============================================================
function _injectTopbar(title, icon) {
    const root = document.getElementById('admin-topbar-root');
    if (!root) return;

    root.outerHTML = `
    <header class="admin-topbar">
        <div class="topbar-title">${icon ? icon + ' ' : ''}${title}</div>
        <div class="topbar-right">
            <div class="topbar-admin-badge">
                <div class="topbar-avatar">A</div>
                <div>
                    <div class="topbar-name">Admin</div>
                    <div class="topbar-role">Quản trị viên</div>
                </div>
            </div>
        </div>
    </header>`;
}

// ============================================================
// Logout Modal
// ============================================================
function _injectLogoutModal() {
    if (document.getElementById('logout-modal')) return;
    const modal = document.createElement('div');
    modal.innerHTML = `
    <div class="modal-overlay" id="logout-modal">
        <div class="modal-box">
            <div class="modal-icon">🚪</div>
            <div class="modal-title">Xác nhận đăng xuất</div>
            <div class="modal-message">Bạn có chắc muốn đăng xuất?</div>
            <div class="modal-actions">
                <button class="btn btn-cancel"
                    onclick="document.getElementById('logout-modal').classList.remove('show')">
                    Hủy
                </button>
                <button class="btn btn-danger" onclick="doLogout()">Đăng xuất</button>
            </div>
        </div>
    </div>`;
    document.body.appendChild(modal.firstElementChild);
}

// ============================================================
// Logout helpers – dùng chung, không cần khai báo lại ở từng trang
// ============================================================
function confirmLogout() {
    document.getElementById('logout-modal').classList.add('show');
}

async function doLogout() {
    await adminLogout(); // từ admin-auth.js
}
