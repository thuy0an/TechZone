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

        if (addresses.length === 0) {
            container.innerHTML = `<p style="color: var(--text-light);">Bạn chưa có địa chỉ nhận hàng nào.</p>`;
            return;
        }

        container.innerHTML = addresses.map(addr => `
            <div class="address-item-profile ${addr.is_default ? 'default' : ''}">
                <div class="address-actions">
                    ${addr.is_default ? '<span class="badge-default">Mặc định</span>' : ''}
                    <button class="btn-text">Sửa</button>
                    ${!addr.is_default ? '<button class="btn-text danger">Xóa</button>' : ''}
                </div>
                <div style="margin-bottom: 8px;">
                    <strong>${addr.receiver_name}</strong> | ${addr.receiver_phone}
                </div>
                <div style="color: var(--text-light); font-size: 0.95rem;">
                    ${addr.address}
                </div>
            </div>
        `).join('');

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
    } catch (error) { console.error('Lỗi tải Tỉnh:', error); }
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
    } catch (error) { console.error('Lỗi tải Quận:', error); }
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
    } catch (error) { console.error('Lỗi tải Phường:', error); }
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
        await apiRequest('/storefront/addresses', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        showNotification('Thêm địa chỉ thành công!');
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