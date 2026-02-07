/**
 * public/js/admin/product.js
 */

let productModal;
let viewProductModal;

document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('productModal');
    if (modalEl) {
        productModal = new bootstrap.Modal(modalEl);
    }

    const viewModalEl = document.getElementById('viewProductModal');
    if (viewModalEl) {
        viewProductModal = new bootstrap.Modal(viewModalEl);
    }

    const form = document.getElementById('productForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
});

// --- MODAL THÊM ---
function openCreateModal() {
    document.getElementById('productForm').reset();
    document.getElementById('p-id').value = '';
    document.getElementById('modalTitle').innerText = 'Thêm sản phẩm mới';

    productModal.show();
}

// --- MODAL CẬP NHẬT ---
function openEditModal(product) {
    document.getElementById('p-id').value = product.id;
    document.getElementById('p-category').value = product.category_id || '';
    document.getElementById('p-brand').value = product.brand_id || '';
    document.getElementById('p-name').value = product.name;
    document.getElementById('p-code').value = product.code;
    document.getElementById('p-price').value = product.current_import_price;
    document.getElementById('p-stock').value = product.stock_quantity;
    document.getElementById('p-description').value = product.description || '';
    document.getElementById('p-profit').value = product.specific_profit_margin || '';
    document.getElementById('p-has-serial').checked = product.has_serial == 1;

    // Xử lý specifications (JSON -> String)
    let specs = '';
    try {
        if (typeof product.specifications === 'string') {
            specs = product.specifications;
        } else {
            specs = JSON.stringify(product.specifications || {});
        }
    } catch (e) { }
    document.getElementById('p-specs').value = specs;

    document.getElementById('p-hidden').checked = product.is_hidden == 1;

    document.getElementById('modalTitle').innerText = 'Cập nhật: ' + product.name;
    productModal.show();
}

// --- MODAL XEM CHI TIẾT ---
function openViewModal(product) {
    document.getElementById('v-name').innerText = product.name;
    document.getElementById('v-code').innerText = product.code;
    document.getElementById('v-category').innerText = product.category ? product.category.name : 'N/A';
    document.getElementById('v-brand').innerText = product.brand ? product.brand.name : 'N/A';
    document.getElementById('v-price').innerText = new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(product.current_import_price);
    document.getElementById('v-stock').innerText = product.stock_quantity;
    document.getElementById('v-profit').innerText = product.specific_profit_margin ? product.specific_profit_margin + '%' : 'Mặc định';
    document.getElementById('v-desc').innerText = product.description || 'Không có mô tả';

    // Xử lý ảnh
    const imgEl = document.getElementById('v-image');
    if (product.image) {
        if (product.image.startsWith('http')) {
            imgEl.src = product.image;
        } else {
            imgEl.src = '/' + product.image;
        }
    } else {
        imgEl.src = 'https://via.placeholder.com/150';
    }

    // Xử lý Specs (JSON)
    const specsDiv = document.getElementById('v-specs');
    specsDiv.innerHTML = ''; // Reset
    // Kiểm tra dữ liệu
    let specs = product.specifications;

    // Nếu vì lý do nào đó nó vẫn là string (do chưa refresh cache model), ta parse nó
    if (typeof specs === 'string') {
        try {
            specs = JSON.parse(specs);
        } catch (e) {
            specs = {};
        }
    }

    // Render ra HTML
    if (specs && typeof specs === 'object' && Object.keys(specs).length > 0) {
        let html = '<ul class="list-unstyled mb-0">';

        // Dùng Object.entries để duyệt qua key-value
        for (const [key, value] of Object.entries(specs)) {
            html += `<li class="mb-1">
                    <span class="fw-bold text-dark">${key}:</span> 
                    <span class="text-secondary">${value}</span>
                 </li>`;
        }
        html += '</ul>';
        specsDiv.innerHTML = html;
    } else {
        specsDiv.innerHTML = '<span class="text-muted fst-italic">Không có thông số kỹ thuật</span>';
    }

    viewProductModal.show();
}


// --- SUBMIT FORM ---
async function handleFormSubmit(e) {
    e.preventDefault();

    const id = document.getElementById('p-id').value;
    const isUpdate = id !== '';
    const formData = new FormData(e.target);

    // Xử lý checkbox (vì nếu không check thì không gửi value)
    formData.set('is_hidden', document.getElementById('p-hidden').checked ? 1 : 0);
    formData.set('has_serial', document.getElementById('p-has-serial').checked ? 1 : 0)

    // Lấy URL từ biến global (được định nghĩa trong Blade)
    let url = isUpdate
        ? ProductConfig.routes.update.replace(':id', id)
        : ProductConfig.routes.store;

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: formData
        });

        const data = await res.json();

        if (res.ok && data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra'));
        }
    } catch (err) {
        console.error(err);
        alert('Lỗi kết nối server');
    }
}

// --- XÓA SẢN PHẨM ---
async function deleteProduct(id) {
    if (!confirm('Bạn chắc chắn muốn xóa sản phẩm này?')) return;

    let url = ProductConfig.routes.delete.replace(':id', id);

    try {
        const res = await fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        });

        const data = await res.json();

        if (res.ok && data.success) {
            document.getElementById('row-' + id).remove();
            alert(data.message);
        } else {
            alert('Lỗi: ' + (data.message || 'Không thể xóa'));
        }
    } catch (err) {
        alert('Lỗi kết nối server');
    }
}