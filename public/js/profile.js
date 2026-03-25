let addressBook = [];
let editingAddressId = null;
let provincesLoaded = false;

document.addEventListener('DOMContentLoaded', async () => {
    if (typeof isLoggedIn === 'function' && !isLoggedIn()) {
        window.location.href = 'login.html';
        return;
    }

    const navButtons = document.querySelectorAll('.sidebar-nav-btn[data-target]');
    const panels = document.querySelectorAll('.section-panel');

    navButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            navButtons.forEach(b => b.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            btn.classList.add('active');
            const targetPanel = document.getElementById(btn.getAttribute('data-target'));
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
        });
    });

    await loadUserProfile();
    await loadUserAddresses();

    loadProvinces();

    const formProfile = document.getElementById('profile-form');
    const btnSaveProfile = document.getElementById('btn-save-profile');

    formProfile.addEventListener('submit', async (e) => {
        e.preventDefault();

        const payload = {
            name: document.getElementById('profile-name').value.trim(),
            phone: document.getElementById('profile-phone').value.trim()
        };

        const originalText = btnSaveProfile.textContent;
        btnSaveProfile.textContent = 'Đang lưu...';
        btnSaveProfile.disabled = true;

        try {
            const response = await apiRequest('/storefront/profile', {
                method: 'PUT',
                body: JSON.stringify(payload)
            });

            if (response.success) {
                showNotification('Cập nhật thông tin thành công!');
                const currentUser = getCurrentUser();
                currentUser.name = payload.name;
                currentUser.phone = payload.phone;
                setSession(getAuthToken(), currentUser);

                // Update UI
                document.getElementById('sidebar-user-name').textContent = payload.name;
                const headerName = document.getElementById('header-user-name');
                if (headerName) headerName.textContent = payload.name;
            }
        } catch (error) {
            showNotification(error.data?.message || 'Có lỗi xảy ra khi cập nhật.');
        } finally {
            btnSaveProfile.textContent = originalText;
            btnSaveProfile.disabled = false;
        }
    });

    const passwordForm = document.getElementById('password-form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', handleChangePassword);
    }
});

// ==========================================
// CÁC HÀM XỬ LÝ THÔNG TIN & ĐỊA CHỈ
// ==========================================

async function loadUserProfile() {
    try {
        const user = getCurrentUser();

        document.getElementById('profile-name').value = user?.name || '';
        document.getElementById('profile-phone').value = user?.phone || '';
        document.getElementById('profile-email').value = user?.email || '';
        document.getElementById('sidebar-user-name').textContent = user?.name || 'Người dùng';

    } catch (error) {
        console.error('Lỗi tải thông tin:', error);
    }
}

async function loadUserAddresses() {
    const container = document.getElementById('address-list-container');
    try {
        const response = await apiRequest('/storefront/addresses', { method: 'GET' });
        const addresses = response.data || [];
        addressBook = addresses;

        if (addresses.length === 0) {
            container.innerHTML = `<p style="color: var(--text-light);">Bạn chưa có địa chỉ nhận hàng nào.</p>`;
            return;
        }

        container.innerHTML = addresses.map(addr => `
            <div class="address-item-profile ${addr.is_default ? 'default' : ''}">
                <div class="address-actions">
                    ${addr.is_default ? '<span class="badge-default">Mặc định</span>' : ''}
                    <button class="btn-text" data-action="edit" data-id="${addr.id}">Sửa</button>
                    ${!addr.is_default ? `<button class="btn-text danger" data-action="delete" data-id="${addr.id}">Xóa</button>` : ''}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>${addr.receiver_name}</strong> | ${addr.receiver_phone}
                </div>
                <div style="color: var(--text-light); font-size: 0.95rem;">
                    ${addr.address}
                </div>
            </div>
        `).join('');

        container.querySelectorAll('button[data-action="edit"]').forEach(btn => {
            btn.addEventListener('click', () => startEditAddress(Number(btn.dataset.id)));
        });
        container.querySelectorAll('button[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', () => handleDeleteAddress(Number(btn.dataset.id)));
        });

    } catch (error) {
        container.innerHTML = `<p style="color: red;">Không thể tải danh sách địa chỉ.</p>`;
    }
}

function toggleAddressForm(show) {
    const listView = document.getElementById('address-list-view');
    const formView = document.getElementById('address-form-view');

    if (show) {
        listView.classList.add('d-none');
        formView.classList.remove('d-none');
        document.getElementById('address_receiver_name').value = document.getElementById('profile-name').value;
        document.getElementById('address_receiver_phone').value = document.getElementById('profile-phone').value;
    } else {
        listView.classList.remove('d-none');
        formView.classList.add('d-none');
        document.getElementById('address-form').reset(); // Xóa form
        editingAddressId = null;
        const title = document.getElementById('address-form-title');
        if (title) title.textContent = 'Thêm địa chỉ mới';
    }
}

// ==========================================
// API DIA CHI NOI BO
// ==========================================

async function loadProvinces() {
    try {
        const data = await apiRequest('/locations/provinces');
        const select = document.getElementById('province');
        select.innerHTML = '<option value="">Chọn Tỉnh thành</option>';
        data.data.forEach(p => select.innerHTML += `<option value="${p.id}">${p.name}</option>`);
        provincesLoaded = true;
        return data.data || [];
    } catch (error) { console.error('Lỗi tải Tỉnh:', error); }
    return [];
}

async function loadDistricts() {
    const provinceId = document.getElementById('province').value;
    const distSelect = document.getElementById('district');
    const wardSelect = document.getElementById('ward');

    distSelect.innerHTML = '<option value="">Chọn Quận huyện</option>';
    wardSelect.innerHTML = '<option value="">Chọn Phường xã</option>';
    wardSelect.disabled = true;

    if (!provinceId) { distSelect.disabled = true; return; }

    try {
        const data = await apiRequest(`/locations/districts?province_id=${provinceId}`);
        data.data.forEach(d => distSelect.innerHTML += `<option value="${d.id}">${d.name}</option>`);
        distSelect.disabled = false;
        return data.data || [];
    } catch (error) { console.error('Lỗi tải Quận:', error); }
    return [];
}

async function loadWards() {
    const districtId = document.getElementById('district').value;
    const wardSelect = document.getElementById('ward');
    wardSelect.innerHTML = '<option value="">Chọn Phường xã</option>';

    if (!districtId) { wardSelect.disabled = true; return; }

    try {
        const data = await apiRequest(`/locations/wards?district_id=${districtId}`);
        data.data.forEach(w => wardSelect.innerHTML += `<option value="${w.id}">${w.name}</option>`);
        wardSelect.disabled = false;
        return data.data || [];
    } catch (error) { console.error('Lỗi tải Phường:', error); }
    return [];
}

async function handleSaveAddress() {
    const btn = document.getElementById('btn-save-address');
    const alertBox = document.getElementById('address-alert');

    const receiver_name = document.getElementById('address_receiver_name').value.trim();
    const receiver_phone = document.getElementById('address_receiver_phone').value.trim();
    const address = document.getElementById('address_detail').value.trim();

    const provEl = document.getElementById('province');
    const distEl = document.getElementById('district');
    const wardEl = document.getElementById('ward');

    const provinceName = provEl.options[provEl.selectedIndex].text;
    const districtName = distEl.options[distEl.selectedIndex].text;
    const wardName = wardEl.options[wardEl.selectedIndex].text;
    const fullAddressParts = [address, wardName, districtName, provinceName].filter(Boolean);
    const payload = {
        receiver_name,
        receiver_phone,
        address: fullAddressParts.join(', ')
    };

    alertBox.style.display = 'none';
    btn.disabled = true;
    btn.textContent = 'Đang lưu...';

    try {
        const requestUrl = editingAddressId ? `/storefront/addresses/${editingAddressId}` : '/storefront/addresses';
        const method = editingAddressId ? 'PUT' : 'POST';

        await apiRequest(requestUrl, {
            method,
            body: JSON.stringify(payload)
        });

        showNotification(editingAddressId ? 'Cập nhật địa chỉ thành công!' : 'Thêm địa chỉ thành công!');
        toggleAddressForm(false);
        loadUserAddresses();

    } catch (err) {
        alertBox.textContent = err.data?.message || 'Lỗi khi lưu địa chỉ. Vui lòng kiểm tra lại thông tin.';
        alertBox.style.display = 'block';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Lưu địa chỉ';
    }
}

function selectOptionByText(select, text) {
    if (!select || !text) return false;
    const options = Array.from(select.options);
    const matched = options.find(opt => opt.text.trim() === text.trim());
    if (matched) {
        select.value = matched.value;
        return true;
    }
    return false;
}

async function startEditAddress(addressId) {
    const address = addressBook.find(item => Number(item.id) === Number(addressId));
    if (!address) return;

    editingAddressId = address.id;
    toggleAddressForm(true);

    const title = document.getElementById('address-form-title');
    if (title) title.textContent = 'Chỉnh sửa địa chỉ';

    document.getElementById('address_receiver_name').value = address.receiver_name || '';
    document.getElementById('address_receiver_phone').value = address.receiver_phone || '';

    const parts = String(address.address || '').split(',').map(p => p.trim()).filter(Boolean);
    const detail = parts.length >= 4 ? parts.slice(0, parts.length - 3).join(', ') : (parts[0] || '');
    const wardName = parts.length >= 3 ? parts[parts.length - 3] : '';
    const districtName = parts.length >= 2 ? parts[parts.length - 2] : '';
    const provinceName = parts.length >= 1 ? parts[parts.length - 1] : '';

    document.getElementById('address_detail').value = detail;

    if (!provincesLoaded) {
        await loadProvinces();
    }

    const provinceSelect = document.getElementById('province');
    selectOptionByText(provinceSelect, provinceName);
    await loadDistricts();

    const districtSelect = document.getElementById('district');
    selectOptionByText(districtSelect, districtName);
    await loadWards();

    const wardSelect = document.getElementById('ward');
    selectOptionByText(wardSelect, wardName);
}

async function handleDeleteAddress(addressId) {
    if (!addressId) return;
    if (!confirm('Bạn có chắc muốn xóa địa chỉ này?')) return;

    try {
        await apiRequest(`/storefront/addresses/${addressId}`, { method: 'DELETE' });
        showNotification('Đã xóa địa chỉ.');
        loadUserAddresses();
    } catch (error) {
        const message = error?.data?.message || 'Không thể xóa địa chỉ.';
        showNotification(message, 'error');
    }
}

async function handleChangePassword(event) {
    event.preventDefault();

    const currentPassword = document.getElementById('current-password')?.value || '';
    const newPassword = document.getElementById('new-password')?.value || '';
    const confirmPassword = document.getElementById('confirm-password')?.value || '';

    if (!currentPassword || !newPassword || !confirmPassword) {
        showNotification('Vui lòng nhập đầy đủ thông tin đổi mật khẩu.', 'error');
        return;
    }

    if (newPassword !== confirmPassword) {
        showNotification('Mật khẩu mới không khớp.', 'error');
        return;
    }

    const btn = document.getElementById('btn-save-password');
    const originalText = btn ? btn.textContent : '';
    if (btn) {
        btn.textContent = 'Đang lưu...';
        btn.disabled = true;
    }

    try {
        await apiRequest('/storefront/profile/password', {
            method: 'PUT',
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword,
            }),
        });

        showNotification('Đổi mật khẩu thành công!');
        event.target.reset();
    } catch (error) {
        const message = error?.data?.message || 'Không thể đổi mật khẩu.';
        showNotification(message, 'error');
    } finally {
        if (btn) {
            btn.textContent = originalText;
            btn.disabled = false;
        }
    }
}