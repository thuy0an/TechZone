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
    { type: 'item', page: 'dashboard',  href: '/admin/dashboard.html',  icon: '📊', label: 'Dashboard' },
    { type: 'section', label: 'Danh mục' },
    { type: 'item', page: 'categories', href: '/admin/categories.html', icon: '📂', label: 'Loại sản phẩm' },
    { type: 'item', page: 'brands',     href: '/admin/brands.html',     icon: '🏷️', label: 'Thương hiệu' },
    { type: 'section', label: 'Quản lý' },
    { type: 'item', page: 'products',   href: '/admin/products.html',   icon: '📦', label: 'Sản phẩm' },
    { type: 'item', page: 'orders',     href: '/admin/orders.html',     icon: '🛒', label: 'Đơn hàng' },
];

// ============================================================
// Main init – gọi 1 lần ở đầu mỗi trang
// ============================================================
/**
 * D – Dependency Inversion: authGuard và renderIdentity được inject từ ngoài.
 *     Mặc định dùng hàm từ admin-auth.js; test có thể truyền stub vào.
 */
function initAdminLayout({
    title          = 'Admin',
    icon           = '',
    activePage     = '',
    authGuard      = requireAdminAuth,
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

    const navHtml = ADMIN_NAV.map(item => {
        if (item.type === 'section') {
            return `<div class="sidebar-section-title">${item.label}</div>`;
        }
        const isActive = item.page === activePage ? 'active' : '';
        return `
            <a href="${item.href}" class="sidebar-nav-item ${isActive}" data-page="${item.page}">
                <span class="nav-icon">${item.icon}</span> ${item.label}
            </a>`;
    }).join('');

    root.outerHTML = `
    <aside class="admin-sidebar">
        <div class="sidebar-brand">
            <span class="sidebar-brand-icon">🛡️</span>
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
            <button class="btn-logout" onclick="confirmLogout()">🚪 Đăng xuất</button>
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
