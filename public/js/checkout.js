let selectedAddressId = null;
let subTotal = 0;
let discountValue = 0;

document.addEventListener('DOMContentLoaded', async () => {
    await loadInitialData();
    setupPaymentListeners();
});

async function loadInitialData() {
    const addrRes = await apiRequest('/storefront/addresses');
    const defaultAddr = addrRes.data.find(a => a.is_default) || addrRes.data[0];
    if (defaultAddr) fillAddressForm(defaultAddr);
    const cartRes = await apiRequest('/storefront/cart');
    subTotal = cartRes.data.items.reduce((sum, item) => sum + (item.price_at_addition * item.quantity), 0);
    document.getElementById('subtotal').innerText = formatVND(subTotal);
    updateFinalTotal();
}

function fillAddressForm(addr) {
    document.getElementById('cust_name').value = addr.receiver_name;
    document.getElementById('cust_phone').value = addr.receiver_phone;
    document.getElementById('cust_province').value = addr.province_name;
    document.getElementById('cust_district').value = addr.district_name;
    document.getElementById('cust_address').value = addr.address;
    selectedAddressId = addr.id;
}

function setupPaymentListeners() {
    document.querySelectorAll('input[name="pay_method"]').forEach(radio => {
        radio.addEventListener('change', (e) => {
            document.getElementById('bank-info').style.display = e.target.value === 'BANK_TRANSFER' ? 'block' : 'none';
            document.getElementById('online-logos').style.display = e.target.value === 'ONLINE' ? 'block' : 'none';
        });
    });
}

function mockOnlinePay(gateway) {
    alert(`Đang chuyển hướng kết nối an toàn tới cổng thanh toán ${gateway}...`);
    window.isMockPaid = true; 
}

/**
 * Áp dụng mã giảm giá cho đơn hàng
 */
async function applyCoupon() {
    const couponInput = document.getElementById('coupon_code');
    const code = couponInput.value.trim();
    const discountEl = document.getElementById('discount');
    const promoSuggestions = document.getElementById('promo-suggestions');

    if (!code) {
        alert("Vui lòng nhập mã giảm giá!");
        return;
    }

   try {
        const response = await apiRequest('/storefront/checkout/apply-promotion', {
            method: 'POST',
            body: JSON.stringify({ 
                promotion_code: code,
                total_amount: subTotal 
            })
        });

        if (response && response.success) {
            discountValue = Number(response.data.discount_amount);
            window.selectedPromotionId = response.data.promotion_id; 
            
            document.getElementById('discount').innerText = `-${formatVND(discountValue)}`;
            updateFinalTotal();
            alert("Áp dụng mã thành công!");
        }
    } catch (error) {
        discountValue = 0;
        window.selectedPromotionId = null; 
        updateFinalTotal();
        alert(error.data?.message || "Lỗi áp mã");
    }
}

function selectPromo(code) {
    const couponInput = document.getElementById('coupon_code');
    if (couponInput) {
        couponInput.value = code;
        applyCoupon();
    }

    async function handleCheckout() {
        const method = document.querySelector('input[name="pay_method"]:checked').value;
        const noteEl = document.getElementById('order_note');
        const noteValue = noteEl ? noteEl.value.trim() : "";
        const shipFee = 30000;


        if (!selectedAddressId) {
            alert('Vui lòng chọn địa chỉ giao hàng!');
            return;
        }
        const finalTotalAmount = Number(subTotal) + Number(shipFee) - Number(discountValue);

        const btnSubmit = document.getElementById('btn-submit-order');
        btnSubmit.disabled = true;
        btnSubmit.innerText = 'ĐANG XỬ LÝ...';

        try {
            const payload = {
                user_address_id: selectedAddressId,
                payment_method: method,
                note: noteValue,
                promotion_id: window.selectedPromotionId || null,
                total_amount: finalTotalAmount,
                shipping_fee: shipFee
            };
            const res = await apiRequest('/storefront/checkout', {
                method: 'POST',
                body: JSON.stringify(payload)
            });

            if (res && res.success) {
                window.location.href = `/thank-you.html?order_id=${res.data.id}`;
            }
        } catch (err) {
            console.error('Lỗi thanh toán:', err);
            alert(err.data?.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            btnSubmit.disabled = false;
            btnSubmit.innerText = 'THANH TOÁN';
        }
    }

    async function openAddressModal() {
        const res = await apiRequest('/storefront/addresses');
        const container = document.getElementById('address-list-container');
        container.innerHTML = res.data.map(addr => `
        <div class="address-option" onclick="selectAddress(${JSON.stringify(addr).replace(/"/g, '&quot;')})">
            <strong>${addr.receiver_name}</strong> - ${addr.receiver_phone}<br>
            <small>${addr.address}, ${addr.ward_name}</small>
        </div>
    `).join('');
        document.getElementById('addressModal').style.display = 'block';
    }

    function closeAddressModal() {
        const modal = document.getElementById('addressModal');
        if (modal) {
            modal.style.display = 'none';
            const quickForm = document.getElementById('quick-address-form');
            if (quickForm) {
                quickForm.reset();
            }
        }
    }
    window.onclick = function (event) {
        const modal = document.getElementById('addressModal');
        if (event.target == modal) {
            closeAddressModal();
        }
    }

    function selectAddress(addr) {
        fillAddressForm(addr);
        document.getElementById('addressModal').style.display = 'none';
    }

    function formatVND(v) { return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(v); }
    function updateFinalTotal() {
        const ship = 30000;
        document.getElementById('final-total').innerText = formatVND(subTotal + ship - discountValue);
    }
}