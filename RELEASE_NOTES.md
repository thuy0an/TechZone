# 🚀 Cập nhật hệ thống: Quản lý Sản phẩm & Cloudinary

Bản cập nhật này tập trung hoàn thiện module quản lý sản phẩm phía Admin, chuyển đổi sang mô hình Hybrid (Blade + AJAX) và tích hợp upload ảnh lên Cloudinary.

## 1. Tính năng mới
- **Cloudinary Integration:** - Đã cài đặt package `cloudinary-labs/cloudinary-laravel`.
  - Ảnh sản phẩm giờ sẽ được upload lên Cloud và lưu URL vào DB.
- **Admin Product CRUD:** - Giao diện quản lý sản phẩm mới sử dụng Modal.
  - Hỗ trợ thêm/sửa/xóa mượt mà không load lại trang.
  - Form nhập liệu khớp hoàn toàn với Database (JSON specs, Serial, Lãi riêng...).
- **Client Homepage:** Trang chủ giờ load dữ liệu thật từ Database thông qua `HomeController`.

## 2. Cập nhật Database
Đã thêm migration mới để bổ sung cột trạng thái:
- Bảng `categories`: Thêm cột `status` (default: true).
- Bảng `brands`: Thêm cột `status` (default: true).

## 3. Refactoring (Tái cấu trúc)
- **Controllers:**
  - `Admin\ProductController`: Kế thừa `BaseApiController` để trả về JSON chuẩn cho AJAX, đồng thời trả về View Blade.
  - Xóa bỏ các Controller API cũ không còn dùng (`OptionController`...).
- **Frontend Assets:**
  - Chuyển các file `.html` tĩnh trong thư mục `public/`. sang dạng cấu trúc của Blade View Engine
  - Tách logic JS admin ra file riêng: `public/js/admin/product.js`.

## 4. Hành động bắt buộc (Action Required)
Thành viên team sau khi pull code về **bắt buộc** phải chạy các lệnh sau:

### 4.1. Cài đặt thư viện mới
```bash
composer install
```

### 4.2. Cập nhật Database
```bash
php artisan migrate
```

### 4.3. Cấu hình .env
```bash
CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
CLOUDINARY_UPLOAD_PRESET=ml_default
```

### 4.4. Xóa Cache (Nếu bị lỗi)
```bash
composer dump-autoload
php artisan route:clear
php artisan view:clear
```