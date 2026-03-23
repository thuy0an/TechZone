
const CLOUDINARY_BASE_URL = "https://res.cloudinary.com/dwmx6maoh/image/upload/v1/";

document.addEventListener('DOMContentLoaded', async () => {
    const urlParams = new URLSearchParams(window.location.search);
    const orderId = urlParams.get('order_id');

    if (!orderId) {
        console.error("Không tìm thấy ID đơn hàng trong URL.");
        alert("Thông tin đơn hàng không hợp lệ!");
        window.location.href = "/";
        return;
    }

    await fetchAndRenderOrder(orderId);
});

/**
 * Gọi API và điều phối việc render dữ liệu
 */
async function fetchAndRenderOrder(orderId) {
    try {
        const response = await apiRequest(`/storefront/orders/${orderId}/summary`);
        
        if (response && response.success) {
            const order = response.data;
            renderCustomerInfo(order);
            renderProductList(order);
            renderPaymentStatus(order);
            
            document.getElementById('order-code-display').innerText = order.order_code;
            document.getElementById('display-total').innerText = formatVND(order.total_amount);
            document.getElementById('display-note').innerText = order.note || "Không có ghi chú cho đơn hàng này.";
        }
    } catch (error) {
        console.error("Lỗi khi tải dữ liệu đơn hàng:", error);
        const wrapper = document.querySelector('.thankyou-wrapper');
        if (wrapper) {
            wrapper.innerHTML = `
                <div class="card text-center" style="padding: 50px;">
                    <h2 class="text-danger">Oops! Không tìm thấy đơn hàng</h2>
                    <p>Có thể đơn hàng không tồn tại hoặc bạn không có quyền xem thông tin này.</p>
                    <a href="/" class="btn-tz-primary">Quay lại trang chủ</a>
                </div>
            `;
        }
    }
}

function renderCustomerInfo(order) {
    const tableBody = document.getElementById('customer-info-table');
    if (!tableBody) return;

    tableBody.innerHTML = `
        <tr>
            <td style="width: 40%;">Người nhận hàng:</td>
            <td><strong>${order.receiver_name}</strong></td>
        </tr>
        <tr>
            <td>Số điện thoại:</td>
            <td>${order.receiver_phone}</td>
        </tr>
        <tr>
            <td>Địa chỉ giao hàng:</td>
            <td>${order.shipping_address}</td>
        </tr>
        <tr>
            <td>Phương thức thanh toán:</td>
            <td>${translatePaymentMethod(order.payment_method)}</td>
        </tr>
    `;
}

function renderProductList(order) {
    const productContainer = document.getElementById('product-summary-list');
    if (!productContainer || !order.details) return;

    productContainer.innerHTML = ''; 
    
    order.details.forEach(item => {
        const productName = item.product ? item.product.name : 'Sản phẩm';
        const rawImg = item.product ? item.product.image : '';
        
        const imgUrl = `https://res.cloudinary.com/dwmx6maoh/image/upload/v1/${item.product.image}`;

        productContainer.innerHTML += `
            <div class="product-summary-item" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                <div style="display: flex; align-items: center;">
                    <img src="${imgUrl}" 
                         alt="${productName}" 
                         style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 15px;"
                         onerror="this.src='https://placehold.co/60x60?text=TechZone'">
                    <div>
                        <div style="font-weight: 600; color: rgb(18, 75, 70);">${productName}</div>
                        <div style="font-size: 0.85rem; color: #666;">Số lượng: ${item.quantity}</div>
                    </div>
                </div>
                <div style="font-weight: 700;">${formatVND(item.unit_price * item.quantity)}</div>
            </div>
        `;
    });
}

function renderPaymentStatus(order) {
    const statusBox = document.getElementById('payment-status-area');
    if (!statusBox) return;

    let content = '';
    const method = order.payment_method.toUpperCase();

    if (method === 'BANK_TRANSFER') {
        content = `
            <div class="payment-status-card" style="background: rgba(18, 75, 70, 0.05); padding: 15px; border-radius: 8px; border-left: 4px solid rgb(18, 75, 70);">
                <h5 style="margin: 0 0 10px 0; color: rgb(18, 75, 70);">🏦 Thông tin chuyển khoản</h5>
                <p style="margin: 5px 0; font-size: 0.9rem;">Ngân hàng: <strong>Vietcombank</strong></p>
                <p style="margin: 5px 0; font-size: 0.9rem;">Số tài khoản: <strong>123456789</strong></p>
                <p style="margin: 5px 0; font-size: 0.9rem;">Chủ TK: <strong>NGUYEN TUAN VU</strong></p>
                <p style="margin: 10px 0 0 0; font-weight: 600; color: #10b981;">● Trạng thái: Chờ xác nhận thanh toán</p>
            </div>
        `;
    } else if (method === 'ONLINE') {
        content = `
            <div class="payment-status-card" style="background: #ecfdf5; padding: 15px; border-radius: 8px; border-left: 4px solid #10b981;">
                <p style="margin: 0; color: #065f46; font-weight: 600;">✅ Trạng thái: Đã thanh toán trực tuyến thành công</p>
            </div>
        `;
    } else {
        content = `<p style="color: #666;">📍 Hình thức: Thanh toán khi nhận hàng (COD)</p>`;
    }

    statusBox.innerHTML = content;
}

function formatVND(value) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
}

function translatePaymentMethod(method) {
    const maps = {
        'COD': 'Tiền mặt khi nhận hàng',
        'BANK_TRANSFER': 'Chuyển khoản ngân hàng',
        'ONLINE': 'Thanh toán trực tuyến'
    };
    return maps[method.toUpperCase()] || method;
}