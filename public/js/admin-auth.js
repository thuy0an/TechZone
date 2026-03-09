/**
 * TechZone Admin – Auth Module
 *
 * S  – Single Responsibility: chỉ chứa authentication logic.
 *      Token storage  → admin-token.js
 *      HTTP transport → admin-api.js
 *
 * Load order: admin-token.js → admin-api.js → admin-auth.js
 */

// ============================================
// Guard: Redirect to login if not authenticated
// ============================================

function requireAdminAuth() {
    if (!isAdminLoggedIn()) {
        window.location.href = '/admin/login.html';
    }
}

// ============================================
// Guard: Redirect to dashboard if already logged in
// Only call on login page
// ============================================

function redirectIfAdminLoggedIn() {
    if (isAdminLoggedIn()) {
        window.location.href = '/admin/dashboard.html';
    }
}

// ============================================
// Login
// ============================================

async function adminLogin(email, password) {
    const data = await adminRequest('/login', {
        method: 'POST',
        body: JSON.stringify({ email, password }),
    });

    if (data.success && data.data) {
        saveAdminSession(data.data.access_token, data.data.admin);
    }

    return data;
}

// ============================================
// Logout
// ============================================

async function adminLogout() {
    try {
        await adminRequest('/logout', { method: 'POST' });
    } catch (_) {
        // Token may be expired — clear anyway
    } finally {
        clearAdminSession();
        window.location.href = '/admin/login.html';
    }
}

// ============================================
// Render admin user info in Sidebar & Topbar
// ============================================

function renderAdminIdentity() {
    const admin = getAdminInfo();
    if (!admin) return;

    const initial = (admin.name || 'A').charAt(0).toUpperCase();

    // Sidebar avatar + name + email
    const avatarEls  = document.querySelectorAll('.admin-avatar, .topbar-avatar');
    const nameEls    = document.querySelectorAll('.admin-user-name');
    const emailEls   = document.querySelectorAll('.admin-user-email');
    const topNameEls = document.querySelectorAll('.topbar-name');

    avatarEls.forEach(el  => el.textContent = initial);
    nameEls.forEach(el    => el.textContent = admin.name  || 'Admin');
    emailEls.forEach(el   => el.textContent = admin.email || '');
    topNameEls.forEach(el => el.textContent = admin.name  || 'Admin');
}

// ============================================
// Toast Notification
// ============================================

function showAdminToast(message, type = 'success', duration = 3000) {
    let toast = document.getElementById('admin-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'admin-toast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }

    toast.className = `toast ${type} show`;
    toast.innerHTML = `<span>${type === 'success' ? '✅' : '❌'}</span> ${message}`;

    setTimeout(() => toast.classList.remove('show'), duration);
}
