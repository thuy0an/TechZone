// public/js/admin/products.js
let globalBrands = [];
let globalCategories = [];

document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    loadOptions();

    // Xử lý tìm kiếm
    let timeout = null;
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                loadProducts(e.target.value);
            }, 500);
        });
    }
});

async function loadProducts(keyword = '') {
    const tableBody = document.getElementById('productTableBody');
    tableBody.innerHTML = '<tr><td colspan="7" class="text-center">⏳ Đang tải dữ liệu...</td></tr>';

    try {
        let url = '/products';
        if (keyword) url += `?search=${encodeURIComponent(keyword)}`;

        const response = await fetch(`${API_BASE_URL}${url}`);
        const result = await response.json();

        const products = result.data ? result.data : result;

        renderTable(products);

    } catch (error) {
        console.error(error);
        tableBody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Lỗi kết nối: ${error.message}</td></tr>`;
    }
}

function renderTable(products) {
    const tableBody = document.getElementById('productTableBody');

    if (!products || products.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center">📭 Không có dữ liệu</td></tr>';
        return;
    }

    const html = products.map(p => {
        let imgUrl = p.image ? p.image : 'https://via.placeholder.com/50';
        if (p.image && !p.image.startsWith('http')) {
            imgUrl = `${window.location.origin}/${p.image}`;
        }

        const stockClass = p.stock_quantity > 0 ? 'stock-in' : 'stock-out';
        const stockText = p.stock_quantity > 0 ? 'Còn hàng' : 'Hết hàng';

        return `
            <tr>
                <td>#${p.id}</td>
                <td><img src="${imgUrl}" class="table-img" alt="${p.code}"></td>
                <td><strong>${p.code}</strong></td>
                <td>${p.name}</td>
                <td>${formatCurrency(p.current_import_price)}</td>
                <td><span class="stock-badge ${stockClass}">${p.stock_quantity}</span></td>
                <td class="text-center">
                    <button class="btn btn-sm btn-info text-white" onclick="viewProduct(${p.id})"><i class="bi bi-eye"></i></button>
                    <button class="btn btn-sm btn-warning" onclick="openEditModal(${p.id})"><i class="bi bi-pencil"></i></button>
                    <button class="btn btn-sm btn-danger" onclick="deleteProduct(${p.id})"><i class="bi bi-trash"></i></button>
                </td>
            </tr>
        `;
    }).join('');

    tableBody.innerHTML = html;
}

window.deleteProduct = async function (id) {
    if (!confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')) return;

    try {
        const token = localStorage.getItem('admin_token');
        const response = await fetch(`${API_BASE_URL}/admin/products/${id}`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            alert('Đã xóa thành công!');
            loadProducts(); // Reload lại bảng
        } else {
            const data = await response.json();
            alert('Lỗi: ' + (data.message || 'Không thể xóa'));
        }
    } catch (error) {
        alert('Lỗi mạng: ' + error.message);
    }
};

async function viewProduct(id) {
    try {
        // Gọi API lấy chi tiết
        const response = await fetch(`${API_BASE_URL}/products/${id}`);

        const result = await response.json();
        const product = result.data;

        if (!response.ok) throw new Error('Không tìm thấy sản phẩm');

        let imgUrl = product.image ? product.image : 'https://via.placeholder.com/300';
        if (product.image && !product.image.startsWith('http')) {
            imgUrl = `${window.location.origin}/${product.image}`;
        }
        document.getElementById('d-image').src = imgUrl;

        const foundBrand = globalBrands.find(b => b.id == product.brand_id);
        const brandName = foundBrand ? foundBrand.name : 'Chưa cập nhật';
        document.getElementById('d-brand').innerText = brandName;

        // 2. Tìm tên Danh mục từ list Global
        const foundCat = globalCategories.find(c => c.id == product.category_id);
        const catName = foundCat ? foundCat.name : 'Chưa cập nhật';
        document.getElementById('d-category').innerText = catName;

        document.getElementById('d-name').innerText = product.name;
        document.getElementById('d-code').innerText = product.code;
        document.getElementById('d-price').innerText = formatCurrency(product.current_import_price);
        document.getElementById('d-stock').innerText = product.stock_quantity;

        document.getElementById('d-status').innerHTML = product.is_hidden
            ? '<span class="badge badge-danger">Đang ẩn</span>'
            : '<span class="badge badge-success">Đang hiện</span>';

        const specsBody = document.getElementById('d-specs');
        specsBody.innerHTML = '';

        if (product.specifications) {
            let specs = product.specifications;

            if (typeof specs === 'string') {
                try {
                    specs = JSON.parse(specs);
                } catch (e) {
                    console.error("Lỗi parse JSON specs:", e);
                    specs = {};
                }
            }
            for (const key in specs) {
                specsBody.innerHTML += `
                    <tr>
                        <td style="font-weight:bold; width: 150px; background:#f9f9f9">${key}</td>
                        <td>${specs[key]}</td>
                    </tr>
                `;
            }
        } else {
            specsBody.innerHTML = '<tr><td>Chưa có thông số kỹ thuật</td></tr>';
        }

        // --- Chuyển View ---
        document.getElementById('section-list').classList.add('d-none');
        document.getElementById('section-detail').classList.remove('d-none');

    } catch (error) {
        console.error(error);
        alert('Lỗi: ' + error.message);
    }
}

// Hàm quay lại
function backToList() {
    document.getElementById('section-detail').classList.add('d-none');
    document.getElementById('section-list').classList.remove('d-none');
}

function openCreateModal() {
    document.getElementById('editForm').reset();
    document.getElementById('edit-id').value = ''; // Xóa ID để hiểu là tạo mới
    document.getElementById('modalTitle').innerText = 'Thêm sản phẩm mới';
    document.getElementById('edit-preview').src = 'https://via.placeholder.com/50';

    const modalEl = document.getElementById('editModal');
    if (!editModalInstance) {
        editModalInstance = new bootstrap.Modal(modalEl);
    }
    editModalInstance.show();
}

// Thông tin Modal sửa sản phẩm
const editModal = document.getElementById('editModal');
let editModalInstance = null;
const editForm = document.getElementById('editForm');

async function loadOptions() {
    try {
        const [resBrand, resCat] = await Promise.all([
            fetch(`${API_BASE_URL}/options/brands`),
            fetch(`${API_BASE_URL}/options/categories`)
        ]);

        const brands = await resBrand.json();
        const categories = await resCat.json();

        globalBrands = brands.data || [];
        globalCategories = categories.data || [];

        const brandSelect = document.getElementById('edit-brand');
        if (brands.data) {
            brands.data.forEach(b => {
                const option = document.createElement('option');
                option.value = b.id;
                option.textContent = b.name;
                brandSelect.appendChild(option);
            });
        }

        const catSelect = document.getElementById('edit-category');
        if (categories.data) {
            categories.data.forEach(c => {
                const option = document.createElement('option');
                option.value = c.id;
                option.textContent = c.name;
                catSelect.appendChild(option);
            });
        }

    } catch (error) {
        console.error('Lỗi tải options:', error);
    }
}

async function openEditModal(id) {
    try {
        const response = await fetch(`${API_BASE_URL}/products/${id}`);
        const result = await response.json();
        const product = result.data;

        document.getElementById('edit-id').value = product.id;
        document.getElementById('edit-name').value = product.name;
        document.getElementById('edit-code').value = product.code;
        document.getElementById('edit-price').value = product.current_import_price;
        document.getElementById('edit-stock').value = product.stock_quantity;
        document.getElementById('edit-hidden').checked = product.is_hidden;
        document.getElementById('edit-category').value = product.category_id;
        document.getElementById('edit-brand').value = product.brand_id;

        let specsVal = product.specifications;
        if (typeof specsVal === 'string') {
            document.getElementById('edit-specs').value = specsVal;
        } else {
            document.getElementById('edit-specs').value = JSON.stringify(specsVal);
        }

        // Preview ảnh cũ
        const imgPreview = document.getElementById('edit-preview');
        if (product.image) {
            imgPreview.src = product.image.startsWith('http') ? product.image : `${window.location.origin}/${product.image}`;
        } else {
            imgPreview.src = 'https://via.placeholder.com/50';
        }

        const modalEl = document.getElementById('editModal');
        document.getElementById('modalTitle').innerText = 'Cập nhật sản phẩm';

        if (!editModalInstance) {
            editModalInstance = new bootstrap.Modal(modalEl);
        }
        editModalInstance.show();

    } catch (error) {
        alert('Lỗi tải dữ liệu: ' + error.message);
    }
}

// function closeEditModal() {
//     editModal.classList.remove('show');
//     editForm.reset();
//     document.getElementById('edit-preview').src = '';
// }

window.onclick = function (event) {
    if (event.target == editModalInstance) {
        editModalInstance.hide();
    }
}

editForm.addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('edit-id').value;
    const token = localStorage.getItem('admin_token');

    const formData = new FormData();
    formData.append('name', document.getElementById('edit-name').value);
    formData.append('code', document.getElementById('edit-code').value);
    formData.append('current_import_price', document.getElementById('edit-price').value);
    formData.append('stock_quantity', document.getElementById('edit-stock').value);
    formData.append('is_hidden', document.getElementById('edit-hidden').checked ? 1 : 0);
    formData.append('category_id', document.getElementById('edit-category').value);

    const brandVal = document.getElementById('edit-brand').value;
    if (brandVal) formData.append('brand_id', brandVal);

    try {
        const specsStr = document.getElementById('edit-specs').value;
        if (specsStr.trim()) {
            JSON.parse(specsStr);
            formData.append('specifications', specsStr);
        }
    } catch (e) {
        alert('Lỗi: Thông số kỹ thuật không đúng định dạng JSON!');
        return;
    }

    const fileInput = document.getElementById('edit-image');
    if (fileInput.files.length > 0) {
        formData.append('image', fileInput.files[0]);
    }

    // Gửi AJAX
    try {
        const response = await fetch(`${API_BASE_URL}/admin/products/${id}`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alert('Cập nhật thành công!');
            editModalInstance.hide();
            loadProducts();
        } else {
            alert('Lỗi: ' + (result.message || 'Không thể cập nhật'));
            console.error(result.errors);
        }
    } catch (error) {
        alert('Lỗi kết nối: ' + error.message);
    }
});