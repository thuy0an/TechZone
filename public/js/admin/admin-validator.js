/**
 * TechZone Admin – Form Validation Module
 *
 * S  – Single Responsibility: chỉ xử lý client-side validation logic.
 * O  – Open/Closed: dễ mở rộng rule mới mà không sửa core.
 * D  – Dependency Inversion: không phụ thuộc DOM cụ thể, nhận config từ ngoài.
 *
 * Load order: admin-token.js → admin-api.js → admin-auth.js → admin-validator.js
 */

// ============================================================
// CORE RULE ENGINE
// ============================================================

/**
 * Tập hợp các rule validation cơ bản.
 * Mỗi rule nhận (value, param) và trả về { valid: bool, message: string }
 */
const AdminRules = {
    required: (value) => ({
        valid: value !== null && value !== undefined && String(value).trim() !== '',
        message: 'Trường này là bắt buộc.'
    }),

    minLength: (value, min) => ({
        valid: String(value).trim().length >= Number(min),
        message: `Tối thiểu ${min} ký tự.`
    }),

    maxLength: (value, max) => ({
        valid: String(value).trim().length <= Number(max),
        message: `Tối đa ${max} ký tự.`
    }),

    email: (value) => ({
        valid: /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(value).trim()),
        message: 'Email không đúng định dạng.'
    }),

    phone: (value) => ({
        valid: /^[0-9]{10,11}$/.test(String(value).trim().replace(/\s/g, '')),
        message: 'Số điện thoại phải có 10-11 chữ số.'
    }),

    numeric: (value) => ({
        valid: !isNaN(parseFloat(value)) && isFinite(value),
        message: 'Phải là số hợp lệ.'
    }),

    min: (value, min) => ({
        valid: parseFloat(value) >= parseFloat(min),
        message: `Giá trị tối thiểu là ${Number(min).toLocaleString('vi-VN')}.`
    }),

    max: (value, max) => ({
        valid: parseFloat(value) <= parseFloat(max),
        message: `Giá trị tối đa là ${Number(max).toLocaleString('vi-VN')}.`
    }),

    positiveInt: (value) => ({
        valid: Number.isInteger(Number(value)) && Number(value) > 0,
        message: 'Phải là số nguyên dương.'
    }),

    nonNegativeInt: (value) => ({
        valid: Number.isInteger(Number(value)) && Number(value) >= 0,
        message: 'Phải là số nguyên không âm.'
    }),

    url: (value) => {
        if (!value || String(value).trim() === '') return { valid: true, message: '' };
        try { new URL(value); return { valid: true, message: '' }; }
        catch { return { valid: false, message: 'URL không hợp lệ.' }; }
    },

    // Giá trị percent 0-100
    percent: (value) => ({
        valid: !isNaN(value) && parseFloat(value) >= 0 && parseFloat(value) <= 100,
        message: 'Phần trăm phải từ 0 đến 100.'
    }),

    // Ngày không được ở quá khứ (tính từ hôm nay)
    futureOrToday: (value) => {
        const d = new Date(value);
        const today = new Date(); today.setHours(0, 0, 0, 0);
        return {
            valid: !isNaN(d.getTime()) && d >= today,
            message: 'Ngày phải từ hôm nay trở đi.'
        };
    },

    // Ngày kết thúc phải sau ngày bắt đầu - rule đặc biệt, cần field thứ 2
    afterField: (value, otherFieldId) => {
        const otherVal = document.getElementById(otherFieldId)?.value;
        if (!otherVal) return { valid: true, message: '' };
        return {
            valid: new Date(value) > new Date(otherVal),
            message: 'Phải sau ngày bắt đầu.'
        };
    },

    // Khác rỗng khi chọn (select)
    selected: (value) => ({
        valid: value !== '' && value !== null && value !== undefined && value !== '0',
        message: 'Vui lòng chọn một giá trị.'
    }),

    // Chỉ chứa chữ cái, số, gạch dưới, gạch ngang
    slug: (value) => ({
        valid: /^[a-zA-Z0-9_\-]+$/.test(String(value).trim()),
        message: 'Chỉ được dùng chữ cái, số, gạch dưới và gạch ngang.'
    }),

    // Mã code sản phẩm: chữ hoa, số
    productCode: (value) => ({
        valid: /^[A-Z0-9\-_]{2,50}$/.test(String(value).trim()),
        message: 'Mã chỉ gồm chữ in hoa, số, dấu gạch (-_). Tối thiểu 2 ký tự.'
    }),

    // Password đủ mạnh
    password: (value) => ({
        valid: String(value).length >= 6,
        message: 'Mật khẩu phải có ít nhất 6 ký tự.'
    }),
};

// ============================================================
// VALIDATION ENGINE
// ============================================================

/**
 * Validate một form theo schema.
 *
 * @param {Object} schema - { fieldId: [ruleString, ...] }
 *   ruleString: 'required' | 'minLength:8' | 'min:0' | ...
 *
 * @returns {{ valid: boolean, errors: { [fieldId]: string } }}
 *
 * @example
 * const result = AdminValidator.validate({
 *   'product-name':  ['required', 'minLength:2', 'maxLength:255'],
 *   'import-price':  ['required', 'numeric', 'min:0'],
 *   'category-id':   ['selected'],
 * });
 * if (!result.valid) {
 *   AdminValidator.showErrors(result.errors);
 *   return;
 * }
 */
const AdminValidator = {

    /**
     * Chạy validate theo schema
     */
    validate(schema) {
        const errors = {};

        for (const [fieldId, rules] of Object.entries(schema)) {
            const el = document.getElementById(fieldId);
            if (!el) continue;

            const value = el.type === 'checkbox' ? (el.checked ? 'true' : '') : (el.value ?? '');

            for (const ruleStr of rules) {
                const [ruleName, ...params] = ruleStr.split(':');
                const ruleFn = AdminRules[ruleName];
                if (!ruleFn) { console.warn(`AdminValidator: unknown rule "${ruleName}"`); continue; }

                const param = params.join(':'); // re-join cho trường hợp afterField có ID chứa ':'
                const result = ruleFn(value, param || undefined);

                if (!result.valid) {
                    errors[fieldId] = result.message;
                    break; // 1 lỗi/field là đủ
                }
            }
        }

        return { valid: Object.keys(errors).length === 0, errors };
    },

    /**
     * Hiển thị lỗi lên DOM.
     * Convention: mỗi input có một <div id="{fieldId}-error" class="field-error">
     */
    showErrors(errors) {
        for (const [fieldId, message] of Object.entries(errors)) {
            const el = document.getElementById(fieldId);
            if (el) {
                el.classList.add('error');
                el.setAttribute('aria-invalid', 'true');
            }
            const errorEl = document.getElementById(`${fieldId}-error`);
            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.add('show');
            }
        }
        // Focus vào field lỗi đầu tiên
        const firstErrorId = Object.keys(errors)[0];
        document.getElementById(firstErrorId)?.focus();
    },

    /**
     * Xóa lỗi hiển thị của toàn bộ form.
     * @param {string[]} fieldIds - mảng ID các field cần clear
     */
    clearErrors(fieldIds) {
        for (const fieldId of fieldIds) {
            const el = document.getElementById(fieldId);
            if (el) {
                el.classList.remove('error');
                el.removeAttribute('aria-invalid');
            }
            const errorEl = document.getElementById(`${fieldId}-error`);
            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.remove('show');
            }
        }
    },

    /**
     * Xóa lỗi ngay khi user thay đổi giá trị (real-time feedback).
     * @param {string[]} fieldIds
     */
    attachClearOnChange(fieldIds) {
        for (const fieldId of fieldIds) {
            const el = document.getElementById(fieldId);
            if (!el) continue;
            const event = (el.tagName === 'SELECT') ? 'change' : 'input';
            el.addEventListener(event, () => {
                el.classList.remove('error');
                el.removeAttribute('aria-invalid');
                const errorEl = document.getElementById(`${fieldId}-error`);
                if (errorEl) { errorEl.textContent = ''; errorEl.classList.remove('show'); }
            }, { passive: true });
        }
    },

    /**
     * Tiện ích: validate & hiển thị lỗi trong một bước.
     * @returns {boolean} true nếu hợp lệ
     */
    run(schema) {
        this.clearErrors(Object.keys(schema));
        const { valid, errors } = this.validate(schema);
        if (!valid) this.showErrors(errors);
        return valid;
    }
};

// ============================================================
// PRESET SCHEMAS (dùng lại cho nhiều form)
// ============================================================

const AdminSchemas = {

    category: {
        'form-category-name': ['required', 'minLength:2', 'maxLength:255'],
    },

    brand: {
        'form-brand-name': ['required', 'minLength:2', 'maxLength:255'],
    },

    product: {
        'form-product-name': ['required', 'minLength:2', 'maxLength:255'],
        'form-product-code': ['required', 'productCode'],
        'form-product-category': ['selected'],
        'form-product-brand': ['selected'],
        'form-product-unit': ['required'],
        'form-product-import-price': ['required', 'numeric', 'min:0'],
        'form-product-margin': ['required', 'numeric', 'min:0', 'max:100'],
        'form-product-stock': ['required', 'nonNegativeInt'],
    },

    supplier: {
        'form-supplier-name': ['required', 'minLength:2', 'maxLength:255'],
        'form-supplier-phone': ['required', 'phone'],
        'form-supplier-email': ['email'],
    },

    importNote: {
        'form-import-supplier': ['selected'],
        'form-import-date': ['required'],
    },

    promotion: {
        'form-promo-name': ['required', 'minLength:2', 'maxLength:255'],
        'form-promo-code': ['required', 'minLength:3', 'maxLength:50'],
        'form-promo-type': ['selected'],
        'form-promo-discount-value': ['required', 'numeric', 'min:1'],
        'form-promo-discount-unit': ['selected'],
        'form-promo-start-date': ['required'],
        'form-promo-end-date': ['required', 'afterField:form-promo-start-date'],
    },

    adminUser: {
        'form-user-name': ['required', 'minLength:2', 'maxLength:255'],
        'form-user-email': ['required', 'email'],
    },
};