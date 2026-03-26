/**
 * TechZone Storefront – Cancel Order Module
 *
 * Cho phép khách hàng hủy đơn hàng đang ở trạng thái "new" (chờ xác nhận).
 * Flow riêng: hiển thị modal xác nhận → gọi API → cập nhật UI.
 */

(function () {
    'use strict';

    // ─── Inject cancel modal vào body (nếu chưa có) ──────────────────────────
    function injectCancelModal() {
        if (document.getElementById('cancel-order-modal')) return;

        const modal = document.createElement('div');
        modal.id = 'cancel-order-modal';
        modal.className = 'cancel-modal-overlay';
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-modal', 'true');
        modal.setAttribute('aria-labelledby', 'cancel-modal-title');
        modal.innerHTML = `
            <div class="cancel-modal-box">
                <div class="cancel-modal-icon">⚠️</div>
                <h3 id="cancel-modal-title">Hủy đơn hàng</h3>
                <p id="cancel-modal-desc">
                    Bạn có chắc muốn hủy đơn <strong id="cancel-modal-code"></strong>?<br>
                    Sau khi hủy, đơn hàng sẽ không thể khôi phục.
                </p>

                <div class="cancel-reason-group">
                    <label for="cancel-reason-select">Lý do hủy đơn <span class="req">*</span></label>
                    <select id="cancel-reason-select" class="cancel-select">
                        <option value="">-- Chọn lý do --</option>
                        <option value="Tôi đổi ý, không muốn mua nữa">Tôi đổi ý, không muốn mua nữa</option>
                        <option value="Tôi muốn thay đổi sản phẩm / số lượng">Tôi muốn thay đổi sản phẩm / số lượng</option>
                        <option value="Thời gian giao hàng quá lâu">Thời gian giao hàng quá lâu</option>
                        <option value="Tôi tìm được giá tốt hơn ở nơi khác">Tôi tìm được giá tốt hơn ở nơi khác</option>
                        <option value="Nhập sai địa chỉ giao hàng">Nhập sai địa chỉ giao hàng</option>
                        <option value="Khác">Khác (nhập bên dưới)</option>
                    </select>
                    <div id="cancel-reason-select-error" class="cancel-field-error"></div>
                </div>

                <div class="cancel-reason-group" id="cancel-other-group" style="display:none">
                    <label for="cancel-reason-other">Lý do cụ thể</label>
                    <textarea id="cancel-reason-other"
                              class="cancel-textarea"
                              placeholder="Mô tả lý do hủy của bạn..."
                              maxlength="500"
                              rows="3"></textarea>
                    <div id="cancel-reason-other-error" class="cancel-field-error"></div>
                </div>

                <div id="cancel-modal-alert" class="cancel-alert" style="display:none"></div>

                <div class="cancel-modal-actions">
                    <button class="cancel-btn-back" id="cancel-modal-back" type="button">
                        Quay lại
                    </button>
                    <button class="cancel-btn-confirm" id="cancel-modal-confirm" type="button">
                        <span class="cancel-spinner" id="cancel-spinner" style="display:none"></span>
                        <span id="cancel-confirm-text">Xác nhận hủy</span>
                    </button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);

        // Đóng khi click overlay
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeCancelModal();
        });

        // ESC đóng modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('show')) closeCancelModal();
        });

        // Toggle lý do khác
        document.getElementById('cancel-reason-select').addEventListener('change', (e) => {
            const otherGroup = document.getElementById('cancel-other-group');
            otherGroup.style.display = e.target.value === 'Khác' ? '' : 'none';
            // Clear error khi user chọn
            document.getElementById('cancel-reason-select-error').textContent = '';
            e.target.classList.remove('invalid');
        });

        // Nút back
        document.getElementById('cancel-modal-back').addEventListener('click', closeCancelModal);

        // Nút xác nhận
        document.getElementById('cancel-modal-confirm').addEventListener('click', () => {
            submitCancelOrder();
        });

        injectCancelStyles();
    }

    // ─── State ────────────────────────────────────────────────────────────────
    let _pendingOrderId = null;
    let _pendingOrderCode = '';
    let _onSuccess = null;

    // ─── Public API ───────────────────────────────────────────────────────────

    /**
     * Mở modal hủy đơn.
     * @param {number} orderId
     * @param {string} orderCode - VD: "ORD20260318001"
     * @param {Function} [onSuccess] - callback sau khi hủy thành công
     */
    window.openCancelOrderModal = function (orderId, orderCode, onSuccess) {
        injectCancelModal();

        _pendingOrderId = orderId;
        _pendingOrderCode = orderCode;
        _onSuccess = onSuccess || null;

        document.getElementById('cancel-modal-code').textContent = orderCode;
        document.getElementById('cancel-reason-select').value = '';
        document.getElementById('cancel-reason-other').value = '';
        document.getElementById('cancel-other-group').style.display = 'none';
        document.getElementById('cancel-reason-select-error').textContent = '';
        document.getElementById('cancel-reason-other-error').textContent = '';
        document.getElementById('cancel-modal-alert').style.display = 'none';

        document.getElementById('cancel-reason-select').classList.remove('invalid');
        document.getElementById('cancel-reason-other').classList.remove('invalid');

        setConfirmLoading(false);

        const modal = document.getElementById('cancel-order-modal');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';

        // Focus vào select
        setTimeout(() => {
            document.getElementById('cancel-reason-select')?.focus();
        }, 120);
    };

    window.closeCancelModal = function () {
        const modal = document.getElementById('cancel-order-modal');
        if (!modal) return;
        modal.classList.remove('show');
        document.body.style.overflow = '';
        _pendingOrderId = null;
        _pendingOrderCode = '';
        _onSuccess = null;
    };

    // ─── Internal ─────────────────────────────────────────────────────────────

    function setConfirmLoading(on) {
        const btn = document.getElementById('cancel-modal-confirm');
        const spinner = document.getElementById('cancel-spinner');
        const text = document.getElementById('cancel-confirm-text');
        if (!btn) return;
        btn.disabled = on;
        spinner.style.display = on ? 'inline-block' : 'none';
        text.textContent = on ? 'Đang xử lý...' : 'Xác nhận hủy';
    }

    function showModalAlert(msg, type = 'error') {
        const el = document.getElementById('cancel-modal-alert');
        el.textContent = msg;
        el.className = `cancel-alert ${type}`;
        el.style.display = '';
    }

    function buildReason() {
        const select = document.getElementById('cancel-reason-select');
        const other = document.getElementById('cancel-reason-other');
        if (select.value === 'Khác') return other.value.trim() || 'Khác';
        return select.value;
    }

    function validateForm() {
        const select = document.getElementById('cancel-reason-select');
        const other = document.getElementById('cancel-reason-other');
        let valid = true;

        document.getElementById('cancel-reason-select-error').textContent = '';
        document.getElementById('cancel-reason-other-error').textContent = '';
        select.classList.remove('invalid');
        other.classList.remove('invalid');

        if (!select.value) {
            document.getElementById('cancel-reason-select-error').textContent = 'Vui lòng chọn lý do hủy đơn.';
            select.classList.add('invalid');
            select.focus();
            valid = false;
        }

        if (valid && select.value === 'Khác' && !other.value.trim()) {
            document.getElementById('cancel-reason-other-error').textContent = 'Vui lòng nhập lý do cụ thể.';
            other.classList.add('invalid');
            other.focus();
            valid = false;
        }

        return valid;
    }

    async function submitCancelOrder() {
        if (!_pendingOrderId) return;
        if (!validateForm()) return;

        const reason = buildReason();
        setConfirmLoading(true);
        document.getElementById('cancel-modal-alert').style.display = 'none';

        try {
            // Gọi API PATCH /storefront/orders/{id}/cancel
            const res = await apiRequest(`/storefront/orders/${_pendingOrderId}/cancel`, {
                method: 'PATCH',
                body: JSON.stringify({ cancel_reason: reason }),
            });

            closeCancelModal();
            showCancelSuccessToast(_pendingOrderCode);

            if (typeof _onSuccess === 'function') _onSuccess(res);

        } catch (err) {
            const msg = err?.data?.message || err?.message || 'Không thể hủy đơn hàng. Vui lòng thử lại.';
            showModalAlert(msg);
            setConfirmLoading(false);
        }
    }

    function showCancelSuccessToast(orderCode) {
        // Dùng showNotification nếu có, fallback tạo toast
        if (typeof showNotification === 'function') {
            showNotification(`Đã hủy đơn hàng ${orderCode} thành công.`, 'success');
            return;
        }

        const toast = document.createElement('div');
        toast.className = 'cancel-toast-success';
        toast.innerHTML = `✅ Đã hủy đơn hàng <strong>${orderCode}</strong> thành công.`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 350);
        }, 3500);
    }

    // ─── Inject CSS ───────────────────────────────────────────────────────────
    function injectCancelStyles() {
        if (document.getElementById('cancel-order-styles')) return;
        const style = document.createElement('style');
        style.id = 'cancel-order-styles';
        style.textContent = `
/* ── Cancel Order Modal ── */
.cancel-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(4px);
    z-index: 9000;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.cancel-modal-overlay.show {
    display: flex;
    animation: cancelFadeIn 0.18s ease;
}
@keyframes cancelFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

.cancel-modal-box {
    background: var(--bg-card, #fff);
    border-radius: 16px;
    padding: 32px 28px 24px;
    max-width: 460px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0,0,0,0.18);
    animation: cancelSlideUp 0.22s cubic-bezier(.22,.61,.36,1);
}
@keyframes cancelSlideUp {
    from { transform: translateY(18px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.cancel-modal-icon {
    font-size: 2.6rem;
    text-align: center;
    margin-bottom: 10px;
    filter: drop-shadow(0 2px 6px rgba(239,68,68,0.25));
}

.cancel-modal-box h3 {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary, #1e293b);
    text-align: center;
    margin: 0 0 8px;
}

.cancel-modal-box p {
    font-size: 0.92rem;
    color: var(--text-secondary, #64748b);
    text-align: center;
    margin: 0 0 20px;
    line-height: 1.6;
}

.cancel-modal-box p strong {
    color: var(--text-primary, #1e293b);
    font-weight: 600;
}

.cancel-reason-group {
    margin-bottom: 14px;
}

.cancel-reason-group label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-primary, #374151);
    margin-bottom: 6px;
}

.cancel-reason-group label .req {
    color: #ef4444;
    margin-left: 2px;
}

.cancel-select,
.cancel-textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid var(--border-color, #e2e8f0);
    border-radius: 8px;
    font-size: 0.9rem;
    background: var(--bg-body, #f8fafc);
    color: var(--text-primary, #1e293b);
    transition: border-color 0.15s, box-shadow 0.15s;
    box-sizing: border-box;
    font-family: inherit;
}

.cancel-select:focus,
.cancel-textarea:focus {
    outline: none;
    border-color: var(--admin-primary, #3b82f6);
    box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
    background: #fff;
}

.cancel-select.invalid,
.cancel-textarea.invalid {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239,68,68,0.10);
}

.cancel-textarea {
    resize: vertical;
    min-height: 72px;
}

.cancel-field-error {
    font-size: 0.78rem;
    color: #ef4444;
    margin-top: 4px;
    min-height: 16px;
}

.cancel-alert {
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 0.88rem;
    margin-bottom: 16px;
    border: 1px solid;
}

.cancel-alert.error {
    background: rgba(239,68,68,0.07);
    color: #dc2626;
    border-color: rgba(239,68,68,0.25);
}

.cancel-modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.cancel-btn-back {
    flex: 1;
    padding: 11px;
    border-radius: 9px;
    border: 1.5px solid var(--border-color, #e2e8f0);
    background: transparent;
    color: var(--text-secondary, #64748b);
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, color 0.15s;
    font-family: inherit;
}

.cancel-btn-back:hover {
    background: var(--bg-body, #f8fafc);
    color: var(--text-primary, #1e293b);
}

.cancel-btn-confirm {
    flex: 1;
    padding: 11px;
    border-radius: 9px;
    border: none;
    background: #ef4444;
    color: #fff;
    font-size: 0.9rem;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: background 0.15s, transform 0.1s;
    font-family: inherit;
}

.cancel-btn-confirm:hover:not(:disabled) {
    background: #dc2626;
    transform: translateY(-1px);
}

.cancel-btn-confirm:disabled {
    opacity: 0.65;
    cursor: not-allowed;
    transform: none;
}

.cancel-spinner {
    width: 16px;
    height: 16px;
    border: 2.5px solid rgba(255,255,255,0.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: cancelSpin 0.7s linear infinite;
    flex-shrink: 0;
}

@keyframes cancelSpin {
    to { transform: rotate(360deg); }
}

/* ── Cancel Success Toast ── */
.cancel-toast-success {
    position: fixed;
    bottom: 28px;
    left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: #10b981;
    color: #fff;
    padding: 14px 24px;
    border-radius: 12px;
    font-size: 0.92rem;
    font-weight: 500;
    box-shadow: 0 8px 30px rgba(16,185,129,0.3);
    z-index: 9999;
    opacity: 0;
    transition: opacity 0.25s, transform 0.25s;
    white-space: nowrap;
}

.cancel-toast-success.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

/* ── Cancel Button (inline in order card) ── */
.btn-cancel-order {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border-radius: 8px;
    border: 1.5px solid #fca5a5;
    background: rgba(239,68,68,0.06);
    color: #dc2626;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, transform 0.1s;
    font-family: inherit;
    text-decoration: none;
}

.btn-cancel-order:hover {
    background: rgba(239,68,68,0.12);
    border-color: #ef4444;
    transform: translateY(-1px);
}

.btn-cancel-order:active {
    transform: none;
}

@media (max-width: 480px) {
    .cancel-modal-box {
        padding: 24px 18px 20px;
    }
    .cancel-modal-actions {
        flex-direction: column-reverse;
    }
}
        `;
        document.head.appendChild(style);
    }

})();