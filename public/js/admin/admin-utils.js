/**
 * TechZone Admin – Shared Utils
 *
 * Tương tự BaseRepository ở backend:
 *   – Các helper dùng chung cho tất cả trang CRUD
 *   – Không phụ thuộc vào trang cụ thể nào
 *
 * Modules:
 *   String:     escHtml, escJs, formatDate
 *   Form:       showFieldError, clearFieldError, clearErrors, setSaveLoading
 *   Pagination: renderPagination
 */

// ============================================================
// String helpers
// ============================================================

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function escJs(str) {
    return String(str)
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'");
}

/**
 * Format ISO date string → dd/mm/yyyy (vi-VN)
 */
function formatDate(iso) {
    if (!iso) return '—';
    const d = new Date(iso);
    return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// ============================================================
// Namespace aliases
// I – Interface Segregation: consumer chỉ phụ thuộc vào nhóm hàm mình cần.
//     Ví dụ: import AdminString.esc thay vì load toàn bộ utils.
// ============================================================
const AdminString = { esc: escHtml, escJs, date: formatDate };
const AdminForm = { showError: showFieldError, clearError: clearFieldError, clearErrors, setLoading: setSaveLoading };
const AdminTable = { renderPagination };

// ============================================================
// Inventory helpers
// ============================================================

const ADMIN_GLOBAL_LOW_STOCK_KEY = 'admin_global_low_stock_threshold';
const ADMIN_GLOBAL_LOW_STOCK_DEFAULT = 5;

function getGlobalLowStockThreshold() {
    const raw = localStorage.getItem(ADMIN_GLOBAL_LOW_STOCK_KEY);
    if (raw === null || raw === '') return ADMIN_GLOBAL_LOW_STOCK_DEFAULT;
    const value = Number(raw);
    return Number.isFinite(value) ? value : ADMIN_GLOBAL_LOW_STOCK_DEFAULT;
}

function setGlobalLowStockThreshold(value) {
    if (value === '' || value === null || typeof value === 'undefined') {
        localStorage.removeItem(ADMIN_GLOBAL_LOW_STOCK_KEY);
        return;
    }
    localStorage.setItem(ADMIN_GLOBAL_LOW_STOCK_KEY, String(value));
}

// ============================================================
// Number format helpers (thousands separator)
// ============================================================

function normalizeNumberString(value) {
    return String(value || '').replace(/\D/g, '');
}

function formatNumberString(value) {
    const raw = normalizeNumberString(value);
    if (!raw) return '';
    return Number(raw).toLocaleString('vi-VN');
}

function parseNumberInputValue(value) {
    const raw = normalizeNumberString(value);
    if (!raw) return null;
    return Number(raw);
}

function bindNumberFormatInputs(root = document) {
    const inputs = root.querySelectorAll('input[type="number"], input[data-number-format]');
    inputs.forEach((input) => {
        if (input.dataset.numberFormatBound === '1') return;

        const step = input.getAttribute('step') || '';
        if (step.includes('.')) return; // allow decimal inputs to keep native behavior

        input.dataset.numberFormatBound = '1';
        input.type = 'text';
        input.inputMode = 'numeric';
        input.autocomplete = 'off';
        input.value = formatNumberString(input.value);

        input.addEventListener('focus', () => {
            input.value = normalizeNumberString(input.value);
        });

        input.addEventListener('input', () => {
            const formatted = formatNumberString(input.value);
            input.value = formatted;
            input.setSelectionRange(formatted.length, formatted.length);
        });

        input.addEventListener('blur', () => {
            input.value = formatNumberString(input.value);
        });
    });
}

// ============================================================
// Modal helpers
// ============================================================

function initAdminModalInteractions() {
    const overlays = document.querySelectorAll('.modal-overlay, .modal-overlay-form');

    overlays.forEach((overlay) => {
        const modal = overlay.querySelector('.modal-box, .modal-form-box');

        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                overlay.classList.remove('show');
            }
        });

        if (modal) {
            modal.addEventListener('click', (event) => {
                event.stopPropagation();
            });
            makeModalDraggable(modal);
        }
    });
}

function makeModalDraggable(modal) {
    const handle = modal.querySelector('.modal-form-header, .modal-header, .modal-title') || modal;
    let startX = 0;
    let startY = 0;
    let startLeft = 0;
    let startTop = 0;
    let isDragging = false;

    const onMove = (event) => {
        if (!isDragging) return;
        const dx = event.clientX - startX;
        const dy = event.clientY - startY;
        modal.style.left = `${startLeft + dx}px`;
        modal.style.top = `${startTop + dy}px`;
    };

    const onUp = () => {
        if (!isDragging) return;
        isDragging = false;
        document.removeEventListener('mousemove', onMove);
        document.removeEventListener('mouseup', onUp);
    };

    handle.addEventListener('mousedown', (event) => {
        if (event.button !== 0) return;
        if (event.target.closest('button, a, input, textarea, select, .modal-close-btn')) return;

        const rect = modal.getBoundingClientRect();
        startX = event.clientX;
        startY = event.clientY;
        startLeft = rect.left;
        startTop = rect.top;

        modal.style.position = 'fixed';
        modal.style.margin = '0';
        modal.style.transform = 'none';
        modal.style.left = `${startLeft}px`;
        modal.style.top = `${startTop}px`;

        isDragging = true;
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
        event.preventDefault();
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initAdminModalInteractions();
    bindNumberFormatInputs();
});

// ============================================================
// Form field error helpers
// ============================================================

/**
 * Hiển thị lỗi dưới một field.
 * Quy ước: input có id=`fieldId`, error div có id=`fieldId-error`
 */
function showFieldError(fieldId, msg) {
    const input = document.getElementById(fieldId);
    if (input) input.classList.add('error');
    const el = document.getElementById(`${fieldId}-error`);
    if (el) { el.textContent = msg; el.classList.add('show'); }
}

function clearFieldError(fieldId) {
    const input = document.getElementById(fieldId);
    if (input) input.classList.remove('error');
    const el = document.getElementById(`${fieldId}-error`);
    if (el) { el.textContent = ''; el.classList.remove('show'); }
}

/**
 * Xóa lỗi nhiều field cùng lúc.
 * Ví dụ: clearErrors('brand-name', 'brand-logo')
 */
function clearErrors(...fieldIds) {
    fieldIds.forEach(clearFieldError);
}

// ============================================================
// Save button loading state
// ============================================================

/**
 * Bật/tắt trạng thái loading cho nút Save.
 *
 * @param {boolean} on
 * @param {object}  opts  – override id mặc định nếu cần
 */
function setSaveLoading(on, {
    btnId = 'save-btn',
    spinnerId = 'save-spinner',
    textId = 'save-btn-text',
    loadingText = 'Đang lưu...',
    idleText = 'Lưu',
} = {}) {
    const btn = document.getElementById(btnId);
    const spinner = document.getElementById(spinnerId);
    const text = document.getElementById(textId);

    if (btn) btn.disabled = on;
    if (spinner) spinner.classList.toggle('show', on);
    if (text) text.textContent = on ? loadingText : idleText;
}

// ============================================================
// Pagination renderer
// ============================================================

/**
 * Render thanh phân trang từ meta của Laravel paginator.
 *
 * D  – Dependency Inversion: dùng event delegation thay vì hardcode tên hàm
 *      `goPage` trong onclick string. onPageChange là callback được inject từ ngoài.
 *
 * @param {object} opts
 *   meta         – object meta từ API (total, last_page, from, to)
 *   currentPage  – trang hiện tại
 *   barId/infoId/btnsId – id các phần tử DOM
 *   onPageChange – callback(page: number)
 * @returns {number} totalPages
 */
function renderPagination({
    meta,
    currentPage,
    barId = 'pagination-bar',
    infoId = 'pagination-info',
    btnsId = 'pagination-btns',
    onPageChange,
} = {}) {
    const bar = document.getElementById(barId);
    const info = document.getElementById(infoId);
    const btns = document.getElementById(btnsId);

    if (!bar) return 1;

    if (!meta || !meta.total) {
        bar.style.display = 'none';
        return 1;
    }

    const totalPages = meta.last_page ?? 1;
    bar.style.display = 'flex';
    if (info) info.textContent = `Hiển thị ${meta.from ?? 1}–${meta.to ?? meta.total} / ${meta.total} mục`;

    if (!btns) return totalPages;

    if (totalPages <= 1) {
        btns.innerHTML = '';
        return totalPages;
    }

    // D: dùng data-page attribute thay vì onclick string hardcode tên hàm
    const mkBtn = (page, label, disabled = false, active = false) =>
        `<button type="button" class="page-btn${active ? ' active' : ''}" ${disabled ? 'disabled' : `data-page="${page}"`
        }>${label}</button>`;

    let html = mkBtn(currentPage - 1, '‹', currentPage === 1);

    for (let p = 1; p <= totalPages; p++) {
        const far = totalPages > 7 && p > 2 && p < totalPages - 1 && Math.abs(p - currentPage) > 1;
        if (far) {
            if (p === 3 || p === totalPages - 2) html += mkBtn(p, '…', true);
            continue;
        }
        html += mkBtn(p, p, false, p === currentPage);
    }

    html += mkBtn(currentPage + 1, '›', currentPage === totalPages);
    btns.innerHTML = html;

    // D: inject callback qua event delegation – không phụ thuộc tên hàm global
    if (typeof onPageChange === 'function') {
        btns.onclick = (e) => {
            const btn = e.target.closest('button[data-page]');
            if (btn && !btn.disabled) onPageChange(parseInt(btn.dataset.page, 10));
        };
    }

    return totalPages;
}
