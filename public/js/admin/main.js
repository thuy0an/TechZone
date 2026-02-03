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
        { name: 'Dashboard', link: 'index.html', icon: 'bi-speedometer2' },
        { name: 'Sản phẩm', link: 'products.html', icon: 'bi-box-seam' },
        { name: 'Đơn hàng', link: 'orders.html', icon: 'bi-receipt' },
        { name: 'Khách hàng', link: 'users.html', icon: 'bi-people' },
    ];

    const currentPath = window.location.pathname;

    const menuHtml = menuItems.map(item => {
        const isActive = currentPath.includes(item.link) ? 'active' : 'text-white';
        return `
            <li class="nav-item">
                <a href="${item.link}" class="nav-link ${isActive}">
                    <i class="bi ${item.icon} me-2"></i> ${item.name}
                </a>
            </li>
        `;
    }).join('');

    placeholder.outerHTML = `
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark sidebar" style="width: 250px; height: 100vh; position: sticky; top: 0;">
            <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <span class="fs-4 fw-bold">TechZone Admin</span>
            </a>
            <hr>
            <ul class="nav nav-pills flex-column mb-auto">
                ${menuHtml}
            </ul>
            <hr>
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
                    <strong>Admin</strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="#">Cài đặt</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="btnLogout">Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    `;

    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) btnLogout.addEventListener('click', setupLogout);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}