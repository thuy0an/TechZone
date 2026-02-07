/**
 * public/js/client/cart.js
 */

let debounceTimer = null;

const Cart = {
    // --- Mở Sidebar và Render dữ liệu ---
    open: function () {
        const myOffcanvas = new bootstrap.Offcanvas(document.getElementById('cartSidebar'));
        myOffcanvas.show();
        this.render();
    },

    // --- Render HTML từ LocalStorage ---
    render: function () {
        const cart = getLocalCart();
        const container = document.getElementById('cart-body');
        const footer = document.getElementById('cart-footer');
        const totalEl = document.getElementById('cart-total');

        if (!cart || !cart.items || cart.items.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-cart-x fs-1 text-muted"></i>
                    <p class="mt-3 text-muted">Giỏ hàng đang trống</p>
                    <button class="btn btn-outline-primary btn-sm mt-2" data-bs-dismiss="offcanvas">Tiếp tục mua sắm</button>
                </div>
            `;
            footer.style.display = 'none';
            return;
        }

        let html = '<ul class="list-group list-group-flush">';
        let totalAmount = 0;

        cart.items.forEach((item, index) => {
            const product = item.product || {};
            const price = parseFloat(product.current_import_price || item.price_at_addition || 0);
            const subtotal = price * item.quantity;
            totalAmount += subtotal;

            let imgUrl = 'https://via.placeholder.com/50';
            if (product.image) {
                imgUrl = product.image.startsWith('http') ? product.image : '/' + product.image;
            }

            html += `
                <li class="list-group-item p-3">
                    <div class="d-flex gap-3">
                        <div style="width: 100px; height: 100px; flex-shrink: 0;">
                            <img src="${imgUrl}" class="img-fluid rounded border" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-1 text-truncate" style="max-width: 180px;">${product.name || 'Sản phẩm ' + item.product_id}</h5>
                                <button onclick="Cart.remove(${item.id}, ${index})" class="btn btn-link text-light bg-danger p-3 text-decoration-none">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                            <p class="mb-2 text-danger fw-bold medium">${formatCurrency(price)}</p>
                            
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <button class="btn btn-outline-secondary px-3" type="button" onclick="Cart.updateQty(${item.id}, ${index}, -1)">-</button>
                                <input type="text" class="form-control text-center bg-white" value="${item.quantity}" readonly>
                                <button class="btn btn-outline-secondary px-3" type="button" onclick="Cart.updateQty(${item.id}, ${index}, 1)">+</button>
                            </div>
                        </div>
                    </div>
                </li>
            `;
        });

        html += '</ul>';
        container.innerHTML = html;

        totalEl.innerText = formatCurrency(totalAmount);
        footer.style.display = 'block';
    },

    // --- Thêm vào giỏ & gọi API lưu vào Database ---
    add: async function (productId, quantity = 1) {
        if (quantity < 1) {
            showToast('Số lượng phải lớn hơn 0', 'error');
            return;
        }

        let cart = getLocalCart();
        let itemIndex = -1;

        if (cart.items) {
            itemIndex = cart.items.findIndex(i => i.product_id == productId);
        } else {
            cart.items = [];
        }

        // Lưu tạm trạng thái cũ để nếu lỗi thì khôi phục 
        const previousCart = JSON.parse(JSON.stringify(cart));

        if (itemIndex > -1) {
            cart.items[itemIndex].quantity = parseInt(cart.items[itemIndex].quantity) + parseInt(quantity);
        } else {
            cart.items.push({
                product_id: productId,
                quantity: parseInt(quantity),
            });
        }

        saveLocalCart(cart);

        // --- Gọi API lưu vào Cart ở Database ---
        document.body.style.cursor = 'wait';

        try {
            const res = await addToCart(productId, quantity);

            if (res && res.success && res.data) {
                // --- TRƯỜNG HỢP THÀNH CÔNG ---
                // Server trả về data chuẩn -> Lưu đè lại Local
                saveLocalCart(res.data);

                showToast('Đã thêm vào giỏ hàng!', 'success');

                const sidebarEl = document.getElementById('cartSidebar');
                if (sidebarEl && sidebarEl.classList.contains('show')) {
                    this.render();
                }
            } else {
                // --- TRƯỜNG HỢP LỖI LOGIC (VD: Hết hàng) ---
                throw new Error(res.message || 'Lỗi không xác định');
            }
        } catch (error) {
            // --- TRƯỜNG HỢP LỖI SERVER/MẠNG ---
            console.error('Lỗi đồng bộ:', error);

            showToast('Lỗi: ' + error.message, 'error');

            // ROLLBACK (Đồng bộ lại dữ liệu thực từ Server)
            await syncCartFromServer();

            this.render();
        } finally {
            document.body.style.cursor = 'default';
        }
    },

    // --- Cập nhật giỏ hàng Local & gọi API lưu vào Database ---
    updateQty: function (itemId, index, delta) {
        let cart = getLocalCart();
        let item = cart.items[index];

        if (!item) return;

        let newQty = parseInt(item.quantity) + delta;
        if (newQty < 1) return;

        // Cập nhật Local Storage & UI
        item.quantity = newQty;
        cart.items[index] = item;

        saveLocalCart(cart);
        this.render();

        // Gửi API ngầm (Debounce 500ms)
        if (debounceTimer) clearTimeout(debounceTimer);

        debounceTimer = setTimeout(async () => {
            console.log('Đang đồng bộ số lượng lên server...');
            try {
                const res = await updateCartItem(itemId, newQty);
                if (res && res.data) {
                    saveLocalCart(res.data);
                }
            } catch (e) {
                console.error('Lỗi đồng bộ:', e);
                showToast('Không thể đồng bộ server', 'error');

                // Nếu lỗi, nên rollback lại (tải lại từ server)
                syncCartFromServer().then(() => this.render());
            }
        }, 500);
    },

    // -- Xóa sản phẩm khỏi giỏ hàng & gọi API lưu vào Database ---
    remove: async function (itemId, index) {
        if (!confirm('Bạn muốn xóa sản phẩm này?')) return;

        let cart = getLocalCart();
        cart.items.splice(index, 1);

        saveLocalCart(cart);
        this.render();

        try {
            await removeCartItem(itemId);
            // Sync lại 
            const res = await syncCartFromServer();
            if (res) this.render();
        } catch (e) {
            console.error(e);
            showToast('Lỗi khi xóa trên server', 'error');
            syncCartFromServer(); // Rollback
        }
    }
};

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('liveToast');
    const toastBody = document.getElementById('toast-message');

    if (toastEl && toastBody) {
        toastBody.innerText = message;

        toastEl.className = `toast align-items-center text-white border-0 ${type === 'success' ? 'bg-success' : 'bg-danger'}`;

        if (typeof bootstrap !== 'undefined') {
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        } else {
            console.error("Bootstrap JS chưa được load!");
            alert(message);
        }
    }
}
