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

- Mục tiêu: xây hệ thống bán hàng + quản trị cho ngành hàng điện tử.
- Trạng thái hiện tại: dự án đang ở giai đoạn nền tảng kiến trúc (base classes, route mẫu, giao diện trang chủ mẫu), chưa hoàn thiện nghiệp vụ theo SRS.

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

### Đã có

- Khung Laravel 12 chạy được.
- Các lớp nền:
  - `app/Http/Controllers/Api/BaseApiController.php`
  - `app/Http/Requests/PaginationRequest.php`
  - `app/Models/BaseModel.php`
  - `app/Repositories/BaseRepository.php` + interface
  - `app/Services/BaseService.php` + interface
- Frontend demo:
  - `public/index.html`
  - `public/js/api.js`
  - `public/js/app.js`
- API test hoạt động: `GET /api/test`.

### Mới ở mức khung / một phần

- Có route bảo vệ bởi Sanctum: `GET /api/user` (cần auth).
- Frontend có hàm gọi `/api/products`, `/api/categories` nhưng backend chưa khai báo route/controller thật.

### Chưa triển khai theo SRS

- Toàn bộ UC nghiệp vụ chính (C1-C4, A1-A4): auth khách hàng/admin đầy đủ, catalog thật, giỏ hàng DB, checkout, promotions, nhập kho, xử lý đơn, báo cáo.
- Bộ migration theo domain TechZone (hiện chỉ có migration mặc định của Laravel).

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

   - Frontend: `http://127.0.0.1:8000/index.html`
   - API test: `http://127.0.0.1:8000/api/test`

> Lưu ý: route `/` hiện redirect về `/index.html`.

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

### API

- `GET /api/test` → kiểm tra API hoạt động
- `GET /api/user` → trả user hiện tại (cần `auth:sanctum`)

## 8) Cấu trúc dự án (rút gọn)

```
TechZone/
├─ app/
│  ├─ Http/
│  │  ├─ Controllers/Api/BaseApiController.php
│  │  └─ Requests/PaginationRequest.php
│  ├─ Models/
│  │  ├─ BaseModel.php
│  │  └─ User.php
│  ├─ Repositories/
│  │  ├─ Interfaces/BaseRepositoryInterface.php
│  │  └─ BaseRepository.php
│  └─ Services/
│     ├─ Interfaces/BaseServiceInterface.php
│     └─ BaseService.php
├─ database/
│  ├─ migrations/ (mặc định Laravel)
│  └─ seeders/
├─ public/
│  ├─ index.html
│  ├─ css/style.css
│  └─ js/{api.js, app.js}
├─ routes/
│  ├─ web.php
│  └─ api.php
├─ TableOfProject.sql
├─ DataMock.sql
└─ ĐẶC TẢ YÊU CẦU HỆ THỐNG PHẦN MỀM.md
```

## 9) Roadmap đề xuất

1. Đồng bộ schema chính thức: chọn chiến lược migration theo SRS (thay vì giữ song song SQL thủ công).
2. Triển khai module Auth (User/Admin, phân quyền, Sanctum).
3. Triển khai Catalog: categories/brands/products + tìm kiếm/lọc/phân trang thật.
4. Triển khai Cart/Checkout/Orders + trạng thái đơn.
5. Triển khai Import Notes + thuật toán giá bình quân + lịch sử giá.
6. Triển khai Promotions + áp mã theo điều kiện.
7. Bổ sung test nghiệp vụ và API test coverage.

---

README này phản ánh **đúng trạng thái code hiện tại** và dùng SRS làm định hướng cho các bước tiếp theo.








