# 📘 Hướng dẫn Test API & Chức năng Quản lý Sản phẩm

Tài liệu này hướng dẫn cách tạo dữ liệu mẫu và test API `Product` trên môi trường local.

---

## 1. Chuẩn bị Dữ liệu (Bắt buộc)
Do chức năng UI quản lý Brand/Category chưa hoàn thiện, cần tạo dữ liệu mẫu bằng CLI trước khi tạo sản phẩm.

**Bước 1:** Mở Terminal tại thư mục dự án, chạy Tinker:
```bash
php artisan tinker
```

**Bước 2:** Chạy lần lượt các lệnh sau để tạo Brand & Category & Admin (nếu chưa có):
```php
// Tạo Danh mục Laptop (ID sẽ là 1)
App\Models\Category::create(['name' => 'Laptop', 'slug' => 'laptop', 'status' => 1]);

// Tạo Thương hiệu Dell (ID sẽ là 1)
App\Models\Brand::create(['name' => 'Dell', 'slug' => 'dell', 'status' => 1]);

// Tạo Admin với email: admin@test.com và password: 123456
App\Models\Admin::create(['name'=>'Admin Tuan', 'email'=>'admin@test.com', 'password'=>'123456', 'role'=>'super_admin']);
```
*Gõ `exit` để thoát.*

---

## 2. Lấy Token Admin (Authorization)
Mọi request tới API sản phẩm đều cần Header `Authorization`.

* **URL:** `POST /api/auth/admin/login`
* **Body:**
    * `email`: `admin@techzone.com`
    * `password`: `password`
* **Kết quả:** Copy chuỗi `access_token` trả về.

---

## 3. Cấu hình Postman

### A. API Tạo Sản phẩm (Create)
* **Method:** `POST`
* **URL:** `/api/admin/products`
* **Auth:** Chọn Type `Bearer Token` -> Paste token vừa lấy.
* **Body:** Chọn `form-data` (để upload ảnh).

| Key | Value (Mẫu) | Type | Ghi chú |
| :--- | :--- | :--- | :--- |
| `category_id` | `1` | Text | ID từ bước 1 |
| `brand_id` | `1` | Text | ID từ bước 1 |
| `name` | `Dell Vostro 3510` | Text | |
| `code` | `DELL-V3510` | Text | **Unique** |
| `current_import_price`| `15000000` | Text | Số nguyên |
| `stock_quantity` | `10` | Text | |
| `specifications` | `{"CPU": "i5", "RAM": "8GB"}` | Text | **JSON String** (cần đúng ngoặc kép) |
| `is_hidden` | `0` | Text | 1: Hiện, 0: Ẩn |
| `image` | *(Chọn file)* | **File** | |

### B. API Cập nhật Sản phẩm (Update)
* **Method:** `POST` (Lưu ý: Không dùng PUT do giới hạn upload file của PHP).
* **URL:** `/api/admin/products/{id}`
* **Body:** `form-data`. Nhập các trường cần sửa.

---

## 4. Các lỗi thường gặp (Troubleshooting)

1.  **Lỗi 422 - specifications field must be a valid JSON:**
    * Sai: `{'CPU': 'i5'}` (Dùng nháy đơn).
    * Đúng: `{"CPU": "i5"}` (Dùng nháy kép).

2.  **Lỗi 500 - Field 'name' doesn't have a default value:**
    * Do chưa chọn đúng tab `form-data` trong Postman hoặc viết sai tên key, thừa khoảng trắng.

3.  **Lỗi "The code has already been taken":**
    * Mã sản phẩm bị trùng. Nếu đang update chính nó thì code backend đã xử lý ignore, kiểm tra lại logic request.