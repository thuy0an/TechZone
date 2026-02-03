document.addEventListener('DOMContentLoaded', () => {
    checkAdminAuth();
    renderSidebar();

    document.body.addEventListener('click', function (e) {
        if (e.target.closest('#btnLogout')) {
            e.preventDefault();
            handleLogout();
        }
    });
});

function checkAdminAuth() {
    const token = localStorage.getItem('admin_token');
    if (!token && !window.location.href.includes('admin-login.html')) {
        alert('Phiên làm việc hết hạn hoặc bạn chưa đăng nhập!');
        window.location.href = '../admin-login.html';
    }
}

function renderSidebar() {
    const placeholder = document.getElementById('sidebar-placeholder');
    if (!placeholder) return;

    // Danh sách menu (Dễ dàng thêm bớt tại đây)
    const menuItems = [
        { name: 'Dashboard', link: 'index.html', icon: 'bi-speedometer2' },
        { name: 'Sản phẩm', link: 'products.html', icon: 'bi-box-seam' },
        { name: 'Đơn hàng', link: 'orders.html', icon: 'bi-receipt' },
        { name: 'Khách hàng', link: 'users.html', icon: 'bi-people' },
    ];

    const currentPath = window.location.pathname.split('/').pop();

    const menuHtml = menuItems.map(item => {
        const isActive = currentPath === item.link || (currentPath === '' && item.link === 'index.html') ? 'active' : 'text-white';

        return `
            <li class="nav-item">
                <a href="${item.link}" class="nav-link ${isActive} d-flex align-items-center mb-2" aria-current="page">
                    <i class="bi ${item.icon} me-3 fs-5"></i>
                    <span>${item.name}</span>
                </a>
            </li>
        `;
    }).join('');

    let adminName = 'Admin';
    const adminInfo = localStorage.getItem('admin_info');
    if (adminInfo) {
        try { adminName = JSON.parse(adminInfo).name; } catch (e) { }
    }

    placeholder.innerHTML = `
        <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark shadow" style="width: 300px; height: 100vh; position: sticky; top: 0; z-index: 1000;">
            <a href="index.html" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                <i class="bi bi-shield-lock-fill fs-3 me-2 text-primary"></i>
                <span class="fs-4 fw-bold">TechZone</span>
            </a>
            <hr class="border-secondary">
            
            <ul class="nav nav-pills flex-column mb-auto">
                ${menuHtml}
            </ul>
            
            <hr class="border-secondary">
            
            <div class="dropdown align-middle p-2">
                <a href="#" class="d-flex align-items-center justify-content-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=${adminName}&background=0D8ABC&color=fff" alt="" width="32" height="32" class="rounded-circle me-2">
                    <span id="admin-name" class="fw-bold text-primary">${adminName}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow text-center" aria-labelledby="dropdownUser1">
                    <li><a class="dropdown-item" href="#">Cài đặt</a></li>
                    <li><a class="dropdown-item" href="#">Hồ sơ</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" id="btnLogout"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a></li>
                </ul>
            </div>
        </div>
    `;
}

function handleLogout() {
    if (confirm('Bạn có chắc chắn muốn đăng xuất?')) {
        localStorage.removeItem('admin_token');
        localStorage.removeItem('admin_info');
        window.location.href = '../admin-login.html';
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}