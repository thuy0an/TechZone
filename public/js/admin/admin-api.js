/**
 * TechZone Admin – HTTP API Client
 *
 * S  – Single Responsibility: chỉ xử lý việc gửi HTTP request đến admin API.
 *      Không biết gì về auth business logic hay DOM.
 *
 * D  – Dependency Inversion: phụ thuộc vào hàm trừu tượng getAdminToken()
 *      (từ admin-token.js), không phụ thuộc trực tiếp vào localStorage.
 *
 * Load order: admin-token.js → admin-api.js → admin-auth.js
 */

/**
 * Gửi JSON request với Bearer token tự động.
 */
async function adminRequest(endpoint, options = {}) {
    const token = getAdminToken();

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...(options.headers || {}),
    };

    const response = await fetch(`${ADMIN_API}${endpoint}`, { ...options, headers });
    const data     = await response.json();

    if (!response.ok) {
        const err    = new Error(data.message || 'Request failed');
        err.status   = response.status;
        err.data     = data;
        throw err;
    }

    return data;
}

/**
 * Gửi multipart/FormData request (dùng cho upload file).
 * Không set Content-Type – để browser tự set multipart boundary.
 * Dùng method POST; truyền _method=PUT vào FormData khi cần update.
 */
async function adminRequestFormData(endpoint, formData) {
    const token    = getAdminToken();
    const response = await fetch(`${ADMIN_API}${endpoint}`, {
        method: 'POST',
        headers: {
            'Authorization': `Bearer ${token}`,
            'Accept':        'application/json',
        },
        body: formData,
    });

    const json = await response.json();

    if (!response.ok) {
        const err  = new Error(json.message || 'Request failed');
        err.status = response.status;
        err.data   = json;
        throw err;
    }

    return json;
}
