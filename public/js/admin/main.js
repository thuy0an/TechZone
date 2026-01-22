document.addEventListener('DOMContentLoaded', () => {
    checkAdminAuth();
    renderSidebar();
    setupLogout();
});

function checkAdminAuth() {
    const token = localStorage.getItem('admin_token');
    if (!token && !window.location.href.includes('admin-login.html')) {
        alert('Phiên làm việc hết hạn hoặc bạn chưa đăng nhập!');
        window.location.href = '../admin-login.html';
    }
}

function setupLogout() {
    const logoutBtn = document.getElementById('btnLogout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            if (confirm('Bạn có chắc muốn đăng xuất?')) {
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_info');
                window.location.href = '../admin-login.html';
            }
        });
    }
}

function renderSidebar() {
    const placeholder = document.getElementById('sidebar-placeholder');
    if (!placeholder) return;

    const menuItems = [
        { name: 'Dashboard', link: 'index.html', icon: '🏠' },
        { name: 'Sản phẩm', link: 'products.html', icon: '📦' },
        { name: 'Đơn hàng', link: 'orders.html', icon: '📄' },
        { name: 'Khách hàng', link: 'users.html', icon: '👥' },
    ];

    const currentPath = window.location.pathname; // VD: /admin/products.html

    const menuHtml = menuItems.map(item => {

        const isActive = currentPath.includes(item.link) ? 'active' : '';
        return `<a href="${item.link}" class="${isActive}">${item.icon} ${item.name}</a>`;
    }).join('');

    placeholder.outerHTML = `
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>TechZone Admin</h3>
            </div>
            <div class="sidebar-menu">
                ${menuHtml}
            </div>
            <div class="sidebar-footer">
                <a href="#" id="btnLogout">🚪 Đăng xuất</a>
            </div>
        </div>
    `;

}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}