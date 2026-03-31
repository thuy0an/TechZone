# TechZone

Nền tảng thương mại điện tử bán thiết bị điện tử, xây dựng trên Laravel 12 (backend API) và Vanilla JS (frontend).

## Thành viên nhóm

| STT | Họ tên | MSSV |
|-----|--------|------|
| 1 | Diệp Thụy An | 3122410001 |
| 2 | Thái Tuấn | 3122410451 |
| 3 | Nguyễn Tuấn Vũ | 3122410483 |
| 4 | Nguyễn Hoàng Ngọc Phong | 3122410310 |

---

## Truy cập nhanh

| Trang | URL |
|-------|-----|
| Storefront | `http://127.0.0.1:8000/index.html` |
| Admin Portal | `http://127.0.0.1:8000/admin/login.html` |
| API Docs | `http://127.0.0.1:8000/docs/api` |

---

## Tech Stack

| Layer | Công nghệ |
|-------|-----------|
| Backend | PHP 8.2+, Laravel 12 |
| Auth | Laravel Sanctum (token-based, tách biệt admin / storefront) |
| Database | MySQL |
| Cache / Queue | Redis (`predis/predis`) |
| Image Upload | Cloudinary (`cloudinary/cloudinary_php`) |
| Frontend | HTML5, CSS3, Vanilla JavaScript (không build step) |
| Testing | Pest 3 + pest-plugin-laravel |
| Code Style | Laravel Pint (PSR-12) |

---

## Cài đặt

### Yêu cầu

- PHP 8.2+
- Composer 2+
- MySQL
- Redis (tuỳ chọn — dùng cho cache và queue)

### Các bước

```bash
# 1. Cài dependencies
composer install

# 2. Tạo file môi trường
cp .env.example .env
php artisan key:generate

# 3. Cấu hình .env
# DB_DATABASE, DB_USERNAME, DB_PASSWORD
# CLOUDINARY_URL (nếu dùng upload ảnh)
# QUEUE_CONNECTION=redis (nếu dùng Redis)

# 4. Khởi tạo database (xem mục Database bên dưới)

# 5. Chạy server
php artisan serve

# 6. (Tuỳ chọn) Chạy queue worker cho import sản phẩm CSV
php artisan queue:listen --tries=1
```

---

## Database

Có 2 cách khởi tạo:

**A. Schema nghiệp vụ đầy đủ (khuyến nghị)**

1. Import `TableOfProject.sql` — tạo DB `techzone_db` và toàn bộ bảng nghiệp vụ.
2. Import `DataMock.sql` — dữ liệu mẫu.
3. Đặt `DB_DATABASE=techzone_db` trong `.env`.

**B. Migration Laravel**

```bash
php artisan migrate
php artisan db:seed
```

> Tài khoản admin mặc định: xem `database/seeders/AdminSeeder.php`.

---

## Cấu trúc dự án

```
TechZone/
├── app/
│   ├── Console/Commands/
│   │   └── SendDailyRevenueReport.php      # Gửi báo cáo doanh thu hàng ngày
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── Admin/                      # Controllers admin
│   │   │   │   ├── Auth/AuthController.php
│   │   │   │   ├── BrandController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── ImportNoteController.php
│   │   │   │   ├── OrderController.php
│   │   │   │   ├── ProductController.php
│   │   │   │   ├── ProductImportController.php
│   │   │   │   ├── PromotionController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   ├── SupplierController.php
│   │   │   │   └── UserController.php
│   │   │   └── Storefront/                 # Controllers storefront
│   │   │       ├── Auth/AuthController.php
│   │   │       ├── AddressController.php
│   │   │       ├── BrandController.php
│   │   │       ├── CartController.php
│   │   │       ├── OrderController.php
│   │   │       ├── ProductController.php
│   │   │       └── ProfileController.php
│   │   ├── Middleware/
│   │   │   └── RequireClientLoginMiddleware.php
│   │   └── Requests/                       # Form Request validation
│   ├── Jobs/
│   │   └── ProcessBulkProductImport.php    # Queue job import CSV
│   ├── Models/
│   │   ├── Admin, User, Brand, Category, Product
│   │   ├── Cart, CartItem
│   │   ├── Order, OrderDetail
│   │   ├── ImportNote, ImportNoteDetail, ImportNotePayment, ImportJob
│   │   ├── ProductPriceHistory
│   │   ├── Promotion
│   │   ├── Supplier
│   │   └── UserAddress
│   ├── Repositories/                       # Data access layer
│   │   ├── Interfaces/                     # Contracts
│   │   └── *.php                           # Implementations (extend BaseRepository)
│   ├── Services/                           # Business logic layer
│   │   ├── Interfaces/                     # Contracts
│   │   ├── Admin/UserService.php
│   │   └── *.php                           # Implementations (extend BaseService)
│   └── Traits/
│       └── ApiResponseTrait.php
├── public/
│   ├── index.html                          # Storefront home
│   ├── products.html                       # Danh sách sản phẩm
│   ├── cart.html                           # Giỏ hàng
│   ├── login.html / register.html          # Auth storefront
│   ├── my-orders.html                      # Lịch sử đơn hàng
│   ├── order-summary.html                  # Xác nhận đơn hàng
│   ├── profile.html                        # Hồ sơ khách hàng
│   ├── setup-address.html                  # Quản lý địa chỉ
│   ├── admin/
│   │   ├── login.html                      # Đăng nhập admin
│   │   ├── dashboard.html                  # Tổng quan
│   │   ├── inventory.html                  # Quản lý tồn kho
│   │   ├── reports.html                    # Báo cáo & thống kê
│   │   ├── products.html                   # Quản lý sản phẩm
│   │   ├── product-price-histories.html    # Lịch sử giá sản phẩm
│   │   ├── categories.html                 # Quản lý danh mục
│   │   ├── brands.html                     # Quản lý thương hiệu
│   │   ├── suppliers.html                  # Quản lý nhà cung cấp
│   │   ├── import-notes.html               # Phiếu nhập hàng
│   │   ├── import-note-action.html         # Tạo / chỉnh sửa phiếu nhập
│   │   ├── orders.html                     # Quản lý đơn hàng
│   │   ├── promotions.html                 # Quản lý khuyến mãi
│   │   ├── users.html                      # Quản lý khách hàng
│   │   └── user-detail.html                # Chi tiết khách hàng
│   ├── css/
│   │   ├── admin.css
│   │   └── style.css
│   └── js/
│       ├── admin/
│       │   ├── admin-token.js              # Quản lý localStorage token
│       │   ├── admin-api.js                # HTTP client (adminRequest)
│       │   ├── admin-auth.js               # Auth guard + login/logout
│       │   ├── admin-layout.js             # Inject sidebar/topbar tự động
│       │   ├── admin-utils.js              # Tiện ích: escape, format, pagination
│       │   ├── admin-validator.js          # Validation helpers
│       │   ├── admin-users.js              # Logic trang quản lý khách hàng
│       │   └── admin-user-detail.js        # Logic trang chi tiết khách hàng
│       ├── api.js                          # HTTP client storefront
│       ├── app.js / app-layout.js          # Layout storefront
│       ├── auth.js                         # Auth storefront
│       ├── cart.js                         # Logic giỏ hàng
│       ├── my-orders.js                    # Logic lịch sử đơn hàng
│       ├── order-summary.js                # Logic xác nhận đơn
│       └── profile.js                      # Logic hồ sơ
├── routes/
│   ├── api.php
│   └── web.php
├── database/
│   ├── migrations/
│   └── seeders/
├── TableOfProject.sql                      # Schema nghiệp vụ đầy đủ
└── DataMock.sql                            # Dữ liệu mẫu
```

---

## Kiến trúc Backend

Mọi module đều theo luồng: `Controller → Service → Repository → Model`

- **Controllers** extend `BaseApiController`, trả về JSON chuẩn qua `successResponse`, `paginatedResponse`, `createdResponse`, `handleException`.
- **Services** extend `BaseService`, inject Repository interface, bọc write trong DB transaction.
- **Repositories** extend `BaseRepository`, inject Eloquent Model, override `applyFilters()` cho query tuỳ chỉnh.
- **Interfaces** nằm trong `Repositories/Interfaces/` và `Services/Interfaces/` — luôn code against interface.

**Chuẩn response API:**
```json
{ "success": true, "message": "...", "data": ... }
```
Response phân trang thêm object `pagination`: `current_page`, `per_page`, `total`, `last_page`.

---

## API Endpoints

### Public

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| GET | `/api/test` | Kiểm tra API |

### Storefront — Public

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/storefront/register` | Đăng ký |
| POST | `/api/storefront/login` | Đăng nhập |
| POST | `/api/storefront/forgot-password` | Quên mật khẩu |
| GET | `/api/storefront/products` | Danh sách sản phẩm |
| GET | `/api/storefront/products/search/basic` | Tìm kiếm cơ bản |
| GET | `/api/storefront/products/search/advanced` | Tìm kiếm nâng cao |
| GET | `/api/storefront/products/category/{id}` | SP theo danh mục |
| GET | `/api/storefront/products/{id}` | Chi tiết sản phẩm |
| GET | `/api/storefront/categories` | Danh sách danh mục |
| GET | `/api/storefront/brands` | Danh sách thương hiệu |
| GET | `/api/locations/provinces` | Danh sách tỉnh/thành |
| GET | `/api/locations/districts` | Danh sách quận/huyện |
| GET | `/api/locations/wards` | Danh sách phường/xã |

### Storefront — Cần xác thực (`auth:sanctum`)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/storefront/logout` | Đăng xuất |
| GET | `/api/storefront/cart` | Xem giỏ hàng |
| POST | `/api/storefront/cart/add` | Thêm vào giỏ |
| POST | `/api/storefront/cart/update` | Cập nhật giỏ |
| DELETE | `/api/storefront/cart/delete/{id}` | Xóa khỏi giỏ |
| GET | `/api/storefront/promotions/active` | Mã khuyến mãi đang hoạt động |
| POST | `/api/storefront/checkout/apply-promotion` | Áp dụng khuyến mãi |
| GET | `/api/storefront/orders` | Lịch sử đơn hàng |
| PATCH | `/api/storefront/orders/{id}/cancel` | Huỷ đơn hàng |
| GET | `/api/storefront/addresses` | Danh sách địa chỉ |
| POST | `/api/storefront/addresses` | Thêm địa chỉ |
| PUT | `/api/storefront/addresses/{id}` | Sửa địa chỉ |
| DELETE | `/api/storefront/addresses/{id}` | Xóa địa chỉ |
| GET | `/api/storefront/profile` | Xem hồ sơ |
| PUT | `/api/storefront/profile` | Cập nhật hồ sơ |
| PUT | `/api/storefront/profile/password` | Đổi mật khẩu |

### Admin — Public

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/admin/login` | Đăng nhập admin |

### Admin — Cần xác thực (`auth:sanctum`)

| Method | Endpoint | Mô tả |
|--------|----------|-------|
| POST | `/api/admin/logout` | Đăng xuất |
| GET/POST | `/api/admin/products` | Danh sách / Tạo sản phẩm |
| GET/PUT/DELETE | `/api/admin/products/{id}` | Chi tiết / Sửa / Xóa |
| GET | `/api/admin/products/{id}/price-histories` | Lịch sử giá |
| GET/POST | `/api/admin/categories` | Danh sách / Tạo danh mục |
| GET/PUT/DELETE | `/api/admin/categories/{id}` | Chi tiết / Sửa / Xóa |
| GET/POST | `/api/admin/brands` | Danh sách / Tạo thương hiệu |
| GET/PUT/DELETE | `/api/admin/brands/{id}` | Chi tiết / Sửa / Xóa |
| GET/POST | `/api/admin/suppliers` | Danh sách / Tạo nhà cung cấp |
| GET/PUT/DELETE | `/api/admin/suppliers/{id}` | Chi tiết / Sửa / Xóa |
| GET | `/api/admin/suppliers/{id}/transaction-history` | Lịch sử giao dịch NCC |
| GET/POST | `/api/admin/import-notes` | Danh sách / Tạo phiếu nhập |
| GET/PUT/DELETE | `/api/admin/import-notes/{id}` | Chi tiết / Sửa / Xóa |
| PUT | `/api/admin/import-notes/{id}/complete` | Hoàn thành phiếu nhập |
| POST | `/api/admin/import-notes/{id}/pay` | Thanh toán phiếu nhập |
| GET | `/api/admin/orders` | Danh sách đơn hàng |
| GET | `/api/admin/orders/{id}` | Chi tiết đơn hàng |
| PUT | `/api/admin/orders/{id}/status` | Cập nhật trạng thái đơn |
| GET/POST | `/api/admin/promotions` | Danh sách / Tạo khuyến mãi |
| GET/PUT/DELETE | `/api/admin/promotions/{id}` | Chi tiết / Sửa / Xóa |
| PATCH | `/api/admin/promotions/{id}/toggle-active` | Bật/tắt khuyến mãi |
| GET | `/api/admin/users` | Danh sách khách hàng |
| POST | `/api/admin/users` | Tạo khách hàng |
| GET | `/api/admin/users/{id}` | Chi tiết khách hàng |
| PUT | `/api/admin/users/{id}` | Cập nhật khách hàng |
| PUT | `/api/admin/users/{id}/lock` | Khoá / mở khoá tài khoản |
| GET | `/api/admin/users/{id}/addresses` | Địa chỉ của khách hàng |
| GET | `/api/admin/reports/revenue-profit` | Báo cáo doanh thu & lợi nhuận |
| GET | `/api/admin/reports/cash-flow` | Báo cáo dòng tiền |
| GET | `/api/admin/reports/best-sellers` | Sản phẩm bán chạy |
| GET | `/api/admin/reports/slow-moving-stock` | Sản phẩm tồn kho chậm |
| GET | `/api/admin/reports/historical-stock` | Lịch sử tồn kho |
| GET | `/api/admin/reports/import-export` | Báo cáo nhập/xuất kho |
| GET | `/api/admin/reports/order-status` | Phân tích trạng thái đơn |
| GET | `/api/admin/reports/sales-by-region` | Doanh thu theo vùng |
| GET | `/api/admin/reports/supplier-payable` | Công nợ nhà cung cấp |
| POST | `/api/admin/imports/upload` | Upload CSV import sản phẩm |
| GET | `/api/admin/imports/{id}/status` | Trạng thái job import |

---

## Lệnh thường dùng

```bash
# Chạy tests
php artisan test

# Chạy tests một lần (không watch)
php artisan test --stop-on-failure

# Fix code style
./vendor/bin/pint

# Xóa cache config
php artisan config:clear

# Chạy queue worker
php artisan queue:listen --tries=1
```
