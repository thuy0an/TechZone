/**
 * public/js/admin/product-manager.js
 */

let productModal;

document.addEventListener('DOMContentLoaded', () => {
    // Khởi tạo Bootstrap Modal
    const modalEl = document.getElementById('productModal');
    if (modalEl) {
        productModal = new bootstrap.Modal(modalEl);
    }

    // Gán sự kiện Submit Form
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