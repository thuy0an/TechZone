/**
 * TechZone Storefront - Layout Module
 * Quản lý việc render Header và Footer dùng chung cho khách hàng.
 */

function initStorefrontLayout({ activePage = 'home' } = {}) {
    _injectHeader(activePage);
    _injectFooter();

    // Cập nhật lại số lượng giỏ hàng sau khi render Header
    if (typeof updateCartCount === 'function') {
        updateCartCount();
    }
}

// ============================================================
// Header (Navbar)
// ============================================================
function _injectHeader(activePage) {
    const root = document.getElementById('storefront-header-root');
    if (!root) return;

    // Lấy trạng thái đăng nhập từ auth.js
    const loggedIn = typeof isLoggedIn === 'function' ? isLoggedIn() : false;
    const user = loggedIn ? getCurrentUser() : null;

    // Render các menu bên trái
    const navLinks = [
        { page: 'home', href: '/', label: 'Trang chủ' },
        { page: 'products', href: '/products.html', label: 'Sản phẩm' }
    ].map(link => `
        <a href="${link.href}" class="nav-link ${activePage === link.page ? 'active' : ''}">
            ${link.label}
        </a>
    `).join('');

    // Render các menu bên phải (User/Auth)
    let rightMenuHtml = '';

    if (loggedIn) {
        // Đã đăng nhập: Hiện giỏ hàng và Dropdown User
        rightMenuHtml = `
            <div style="display: flex; align-items: center; gap: 20px;">
                <a href="/cart.html" class="nav-link ${activePage === 'cart' ? 'active' : ''}" 
                style="position: relative; width: 44px; height: 44px;">
                    <span style="font-size: 1.4rem;">🛒</span> 
                    <span class="badge" id="cart-count" style="position: absolute; top: -5px; right: -5px;">0</span>
                </a>

                <div class="user-dropdown" style="position: relative;">
                    <div class="user-profile-toggle" style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <div style="width: 32px; height: 32px; background: #e2e8f0; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            👤
                        </div>
                        <div style="display: flex; flex-direction: column;">
                            <span id="header-user-name" style="font-size: 1rem; font-weight: 700;">${user?.name || 'Tài khoản'}</span>
                            <small style="color: #718096; font-size: 0.9rem;">Thành viên ▼</small>
                        </div>
                    </div>
                    
                    <div class="dropdown-content">
                        <a href="/my-orders.html">📦 Lịch sử mua hàng</a>
                        <a href="/profile.html">⚙️ Thiết lập tài khoản</a>
                        <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 5px 0;">
                        <button onclick="logoutCustomer()" class="btn-logout-storefront">
                            🚪 Đăng xuất
                        </button>
                    </div>
                </div>
            </div>
        `;
    } else {
        // Chưa đăng nhập: Hiện nút Đăng ký / Đăng nhập
        rightMenuHtml = `
            <div style="display: flex; align-items: center; gap: 35px;">
                <a href="/register.html" class="nav-link">Đăng ký</a>
                <a href="/login.html" class="btn btn-login">Đăng nhập</a>
            </div>
        `;
    }

    // Đổ HTML vào DOM
    root.outerHTML = `
    <header class="header">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
            
            <div style="flex: 1; display: flex; justify-content: flex-start;">
                <a href="/" class="logo">
                    <span class="logo-icon">📱</span>
                    TechZone
                </a>
            </div>

            <div style="flex: 1; display: flex; justify-content: center;">
                <nav class="nav">
                    ${navLinks}
                </nav>
            </div>

            <div style="flex: 1; display: flex; justify-content: flex-end;">
                ${rightMenuHtml}
            </div>

        </div>
    </header>`;
}

// ============================================================
// Footer
// ============================================================
function _injectFooter() {
    const root = document.getElementById('storefront-footer-root');
    if (!root) return;

    root.outerHTML = `
    <footer class="footer">
        <div class="container">
            <p>&copy; ${new Date().getFullYear()} TechZone. Tất cả quyền được bảo lưu.</p>
            <p style="font-size: 0.85rem; margin-top: 5px;">Hệ thống phân phối các thiết bị công nghệ chính hãng.</p>
        </div>
    </footer>`;
}

document.addEventListener('click', function (e) {
    const toggle = e.target.closest('.user-profile-toggle');
    const dropdown = e.target.closest('.user-dropdown');
    const allMenus = document.querySelectorAll('.dropdown-content');

    if (toggle && dropdown) {
        const menu = dropdown.querySelector('.dropdown-content');
        const isOpen = menu.classList.contains('show');

        allMenus.forEach(m => m.classList.remove('show'));

        if (!isOpen) {
            menu.classList.add('show');
        }

        e.stopPropagation();
    }
    else if (e.target.closest('.dropdown-content')) {
        e.stopPropagation();
    }
    else {
        allMenus.forEach(m => m.classList.remove('show'));
    }
});;