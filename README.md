# TechZone

TechZone là dự án website kinh doanh thiết bị điện tử, xây trên Laravel 12 (backend)


## 👥 Thành Viên Nhóm

| STT | Họ tên | MSSV |
|-----|--------|------|
| 1 | Diệp Thụy An | 3122410001 |
| 2 | Thái Tuấn | 3122410451 |
| 3 | Nguyễn Tuấn Vũ | 3122410483 |
| 4 | Nguyễn Hoàng Ngọc Phong | 3122410310 |




## 1) Tổng quan

- **Mục tiêu:** Xây hệ thống bán hàng + quản trị cho ngành hàng điện tử.
- **Trạng thái hiện tại:** Đã triển khai Admin Portal (xác thực, CRUD danh mục & thương hiệu) và nền tảng Storefront (xác thực khách hàng, giỏ hàng, đặt hàng). Backend theo kiến trúc Repository + Service. Frontend Admin theo chuẩn SOLID với các module dùng chung.

### Truy cập nhanh

| Đối tượng | URL |
|-----------|-----|
| **Trang Admin** | `http://127.0.0.1:8000/admin/login.html` |
| **Trang Khách hàng** | `http://127.0.0.1:8000/index.html` |
| **Trang API Docs** | `http://127.0.0.1:8000/docs/api` |

## 2) Phạm vi theo SRS

1. **Storefront (Khách hàng)**
   - Đăng ký/đăng nhập
   - Tìm kiếm/lọc sản phẩm
   - Giỏ hàng, checkout, thanh toán
   - Lịch sử mua hàng

2. **Admin Portal (Quản trị)**
   - Quản lý sản phẩm, danh mục, thương hiệu
   - Nhập kho, tính giá bình quân, cập nhật giá bán
   - Quản lý khuyến mãi, đơn hàng, báo cáo tồn kho
   - Nghiệp vụ tồn kho theo sản phẩm/lô (mở rộng theo SRS)

## 3) Mức độ triển khai hiện tại so với SRS

### Đã hoàn thiện

**Backend (API)**
- Khung Laravel 12 + kiến trúc Repository/Service Pattern với base class cho tất cả module.
- **Admin Auth:** đăng nhập, đăng xuất, Sanctum token (`/api/admin/login`, `/api/admin/logout`).
- **Storefront Auth:** đăng ký, đăng nhập, đăng xuất khách hàng.
- **Category (Admin):** CRUD đầy đủ, tìm kiếm, phân trang.
- **Brand (Admin):** CRUD đầy đủ, upload logo Cloudinary, tìm kiếm, phân trang.
- **Product (Admin/Storefront):** danh sách, chi tiết, quản lý sản phẩm.
- **Cart:** xem, thêm, xóa sản phẩm khỏi giỏ.
- **Order:** đặt hàng (checkout), lịch sử đơn hàng, quản lý đơn cho admin.

**Frontend Admin** (`public/admin/`)
- Kiến trúc SOLID — 5 module JS dùng chung:
  - `admin-token.js` — quản lý localStorage token (S)
  - `admin-api.js` — HTTP client (S)
  - `admin-auth.js` — guards, login/logout (S)
  - `admin-layout.js` — inject sidebar/topbar/modal tự động (O, D)
  - `admin-utils.js` — tiện ích: escape, format, pagination (I, D)
- Các trang: `login.html`, `dashboard.html`, `categories.html`, `brands.html`.

### Chưa triển khai theo SRS

- Trang sản phẩm admin (`products.html`) và trang đơn hàng admin (`orders.html`).
- Quản lý nhập kho, tính giá bình quân, cập nhật giá bán.
- Quản lý khuyến mãi/mã giảm giá.
- Báo cáo tồn kho.
- Giao diện Storefront chi tiết (tìm kiếm/lọc, giỏ hàng UI, checkout UI).

## 4) Tech stack

- **Backend:** PHP 8.2+, Laravel 12
- **Frontend:** HTML5, CSS3, JavaScript (vanilla)
- **Database:** MySQL (khuyến nghị chạy qua XAMPP)
- **Test:** Pest/PHPUnit (mặc định từ Laravel skeleton)

## 5) Cài đặt trên Windows + XAMPP

### Yêu cầu

- XAMPP (Apache + MySQL)
- PHP 8.2+ (nên dùng bản PHP của XAMPP hoặc PHP hệ thống tương thích)
- Composer 2+

### Các bước

1. Mở XAMPP Control Panel, **Start Apache** và **Start MySQL**.
2. Tại thư mục dự án, cài dependency:

   `composer install`

3. Tạo file môi trường:

   - Sao chép `.env.example` thành `.env`
   - Sinh key:

   `php artisan key:generate`

4. Cấu hình DB trong `.env` (DB_HOST/DB_PORT/DB_DATABASE/DB_USERNAME/DB_PASSWORD).
5. Chạy ứng dụng:

   `php artisan serve`

6. Truy cập:

   | Trang | URL |
   |-------|-----|
   | **Admin** (đăng nhập quản trị) | `http://127.0.0.1:8000/admin/login.html` |
   | **Khách hàng** (storefront) | `http://127.0.0.1:8000/index.html` |
   | API test | `http://127.0.0.1:8000/api/test` |

> Tài khoản admin mặc định: xem file `database/seeders/AdminSeeder.php`.

## 6) Tùy chọn cơ sở dữ liệu

Có 2 hướng khởi tạo DB:

### A. Dùng migration mặc định (nhanh để chạy skeleton)

1. Tạo DB rỗng (ví dụ `techzone`).
2. Cập nhật `.env` trỏ đến DB đó.
3. Chạy:

   `php artisan migrate`

Kết quả: tạo các bảng mặc định Laravel (`users`, `sessions`, `cache`, `jobs`, ...), **chưa phải schema nghiệp vụ đầy đủ theo SRS**.

### B. Dùng schema nghiệp vụ + dữ liệu mẫu

1. Import `TableOfProject.sql` (script sẽ tạo DB `techzone_db` và các bảng nghiệp vụ).
2. Import tiếp `DataMock.sql` để có dữ liệu mẫu.
3. Cập nhật `.env`:

   - `DB_DATABASE=techzone_db`

4. Không chạy `migrate` lên cùng DB này nếu chưa rà soát xung đột schema.

## 7) Endpoints hiện có

### Web

- `GET /` → redirect `/index.html`

### API — Admin (cần Bearer token admin)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/admin/login` | Đăng nhập admin |
| POST | `/api/admin/logout` | Đăng xuất admin |
| GET/POST | `/api/admin/categories` | Danh sách / Tạo danh mục |
| GET/PUT/DELETE | `/api/admin/categories/{id}` | Chi tiết / Sửa / Xóa danh mục |
| GET/POST | `/api/admin/brands` | Danh sách / Tạo thương hiệu |
| GET/PUT/DELETE | `/api/admin/brands/{id}` | Chi tiết / Sửa / Xóa thương hiệu |
| GET/POST | `/api/admin/products` | Danh sách / Tạo sản phẩm |
| GET/PUT/DELETE | `/api/admin/products/{id}` | Chi tiết / Sửa / Xóa sản phẩm |
| GET | `/api/admin/orders` | Danh sách đơn hàng |
| PUT | `/api/admin/orders/{id}/status` | Cập nhật trạng thái đơn |

### API — Storefront (public)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/test` | Kiểm tra API |
| POST | `/api/storefront/register` | Đăng ký khách hàng |
| POST | `/api/storefront/login` | Đăng nhập khách hàng |
| GET | `/api/storefront/products` | Danh sách sản phẩm |
| GET | `/api/storefront/products/{id}` | Chi tiết sản phẩm |
| GET | `/api/storefront/categories` | Danh sách danh mục |

### API — Storefront (cần Bearer token khách hàng)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/storefront/cart` | Xem giỏ hàng |
| POST | `/api/storefront/cart/add` | Thêm vào giỏ |
| DELETE | `/api/storefront/cart/delete/{id}` | Xóa khỏi giỏ |
| POST | `/api/storefront/checkout` | Đặt hàng |
| GET | `/api/storefront/orders` | Lịch sử đơn hàng |
| POST | `/api/storefront/logout` | Đăng xuất |

## 8) Cấu trúc dự án (rút gọn)

```
TechZone/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/Api/
│  │  │  ├─ Admin/   (AuthController, CategoryController, BrandController, ProductController, OrderController)
│  │  │  └─ Storefront/ (AuthController, ProductController, CartController, OrderController)
│  │  ├─ Requests/   (form validation cho từng module)
│  │  └─ Resources/  (JSON transformers)
│  ├─ Models/        (Admin, User, Product, Brand, Category, Cart, CartItem, Order, OrderDetail, ImportNote)
│  ├─ Repositories/  (Base + Admin/Order/Cart/Category/Product/User + Interfaces)
│  └─ Services/      (Base + AdminAuth/AdminOrder/Auth/Cart/Category/Cloudinary/Order/Product + Interfaces)
├─ database/
│  ├─ migrations/
│  └─ seeders/       (AdminSeeder, ...)
├─ public/
│  ├─ index.html                    ← Storefront (trang khách hàng)
│  ├─ admin/
│  │  ├─ login.html                 ← Trang đăng nhập admin
│  │  ├─ dashboard.html
│  │  ├─ categories.html
│  │  └─ brands.html
│  ├─ css/admin.css
│  └─ js/
│     ├─ admin-token.js             ← module: localStorage token
│     ├─ admin-api.js               ← module: HTTP client
│     ├─ admin-auth.js              ← module: auth guards + login/logout
│     ├─ admin-layout.js            ← module: inject sidebar/topbar
│     └─ admin-utils.js             ← module: tiện ích dùng chung
├─ routes/
│  ├─ web.php
│  └─ api.php
├─ TableOfProject.sql
└─ DataMock.sql
```

## 9) Roadmap đề xuất

- [x] Khung Laravel 12 + base classes (Repository/Service Pattern)
- [x] Admin Auth (đăng nhập/đăng xuất, Sanctum token)
- [x] Storefront Auth (đăng ký/đăng nhập khách hàng)
- [x] CRUD Category (admin) — backend + UI
- [x] CRUD Brand + upload logo (admin) — backend + UI
- [x] Product management — backend API
- [x] Cart & Checkout — backend API
- [x] Order management — backend API
- [x] Frontend Admin SOLID module system
- [ ] Trang sản phẩm admin (`products.html`) + trang đơn hàng admin (`orders.html`)
- [ ] Storefront UI chi tiết (tìm kiếm/lọc, giỏ hàng, checkout)
- [ ] Nhập kho + thuật toán giá bình quân + lịch sử giá
- [ ] Quản lý khuyến mãi / mã giảm giá
- [ ] Báo cáo tồn kho
- [ ] Test coverage (Pest/PHPUnit)

---

README này phản ánh **đúng trạng thái code hiện tại** và dùng SRS làm định hướng cho các bước tiếp theo.








