# 🛒 TechZone - Dự án Website Bán Hàng Công Nghệ

> Dự án web thương mại điện tử sử dụng **Laravel 12** làm Backend API và **HTML/CSS/JavaScript** làm Frontend.

---

## 👥 Thành Viên Nhóm

| STT | Họ tên | MSSV |
|-----|--------|------|
| 1 | Diệp Thụy An | 3122410001 |
| 2 | Thái Tuấn | 3122410451 |
| 3 | Nguyễn Tuấn Vũ | 3122410483 |
| 4 | Nguyễn Hoàng Ngọc Phong | 3122410310 |

---

## 📋 Mục Lục

- [Giới thiệu](#-giới-thiệu)
- [Kiến trúc dự án](#-kiến-trúc-dự-án)
- [Yêu cầu hệ thống](#-yêu-cầu-hệ-thống)
- [Cài đặt](#-cài-đặt)
- [Cấu trúc dự án](#-cấu-trúc-dự-án)
- [Các lệnh thường dùng](#-các-lệnh-thường-dùng)
- [Tài liệu tham khảo](#-tài-liệu-tham-khảo)

---

## 🎯 Giới Thiệu

**TechZone** là dự án website bán hàng công nghệ, được xây dựng với:

| Thành phần | Công nghệ |
|------------|-----------|
| **Backend** | Laravel 12 (PHP 8.2+) - REST API |
| **Frontend** | HTML + CSS + JavaScript thuần |
| **Database** | MySQL |

---

## 🏗️ Kiến Trúc Dự Án

Dự án sử dụng mô hình **Client-Server** với API:

```
┌─────────────────────────────────────────────────────────────────┐
│                        FRONTEND                                  │
│   📄 HTML + 🎨 CSS + ⚡ JavaScript                               │
│   (Đặt trong thư mục public/)                                   │
└─────────────────────┬───────────────────────────────────────────┘
                      │ HTTP Request (fetch/AJAX)
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                     LARAVEL BACKEND (API)                        │
│                                                                  │
│   📂 routes/api.php     → Định nghĩa API endpoints              │
│   📂 Controllers/       → Xử lý logic, trả về JSON              │
│   📂 Models/            → Tương tác với Database                 │
└─────────────────────┬───────────────────────────────────────────┘
                      │
                      ▼
┌─────────────────────────────────────────────────────────────────┐
│                        DATABASE (MySQL)                          │
│   Bảng: users, products, categories, orders, ...                │
└─────────────────────────────────────────────────────────────────┘
```

### 🔄 Luồng hoạt động:
1. **User** mở trang HTML trong trình duyệt
2. **JavaScript** gọi API Laravel (ví dụ: `GET /api/products`)
3. **Laravel Controller** xử lý request, lấy dữ liệu từ Database
4. **Laravel** trả về dữ liệu dạng **JSON**
5. **JavaScript** nhận JSON và render lên giao diện HTML

---

## 💻 Yêu Cầu Hệ Thống

| Công cụ | Phiên bản tối thiểu |
|---------|---------------------|
| PHP | 8.2 trở lên |
| Composer | 2.x |
| MySQL | 8.0 trở lên |
| XAMPP/Laragon | Phiên bản mới nhất |
| Trình duyệt | Chrome, Firefox, Edge |

---

## 🚀 Cài Đặt

### Bước 1: Clone dự án
```bash
git clone <repository-url>
cd TechZone
```

### Bước 2: Cài đặt PHP dependencies
```bash
composer install
```

### Bước 3: Cấu hình môi trường
```bash
# Copy file .env.example thành .env
cp .env.example .env

# Tạo APP_KEY
php artisan key:generate
```

### Bước 4: Cấu hình Database
Mở file `.env` và sửa thông tin database:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techzone
DB_USERNAME=root
DB_PASSWORD=
```

### Bước 5: Tạo database và chạy migration
```bash
# Tạo database "techzone" trong MySQL (dùng phpMyAdmin hoặc terminal)

# Chạy migration để tạo các bảng
php artisan migrate
```

### Bước 6: Chạy ứng dụng
```bash
# Chạy Laravel server
php artisan serve

# Truy cập: http://localhost:8000
```

---

## 📁 Cấu Trúc Dự Án

```
TechZone/
│
├── 📂 app/                             # 🔥 CODE PHP CHÍNH (Backend)
│   │
│   ├── 📂 Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php          # Laravel base controller
│   │   │   └── Api/
│   │   │       └── BaseApiController.php   # ⭐ Base API với response helpers
│   │   └── Requests/
│   │       └── PaginationRequest.php   # Validation cho pagination
│   │
│   ├── 📂 Models/                      # Eloquent Models
│   │   ├── BaseModel.php               # ⭐ Base với soft delete, search
│   │   └── User.php
│   │
│   ├── 📂 Repositories/                # Repository Pattern
│   │   ├── Interfaces/
│   │   │   └── BaseRepositoryInterface.php
│   │   └── BaseRepository.php          # ⭐ CRUD, pagination, filtering
│   │
│   ├── 📂 Services/                    # Business Logic Layer
│   │   ├── Interfaces/
│   │   │   └── BaseServiceInterface.php
│   │   └── BaseService.php             # ⭐ Transactions + lifecycle hooks
│   │
│   ├── 📂 Traits/
│   │   └── ApiResponseTrait.php        # Response methods dùng chung
│   │
│   └── 📂 Providers/                   # Service providers
│
├── 📂 bootstrap/
│   └── app.php                         # Đăng ký routes (api.php + web.php)
│
├── 📂 config/                          # ⚙️ Cấu hình ứng dụng
│
├── 📂 database/                        # 🗄️ Database
│   ├── migrations/                     # Tạo/sửa cấu trúc bảng
│   ├── seeders/                        # Dữ liệu mẫu
│   └── factories/                      # Dữ liệu test
│
├── 📂 public/                          # 🌐 FRONTEND (HTML/CSS/JS)
│   ├── index.html                      # Trang chủ
│   ├── css/style.css                   # Styles
│   └── js/
│       ├── api.js                      # Gọi API
│       └── app.js                      # Logic frontend
│
├── 📂 routes/                          # 🛣️ Routes
│   ├── api.php                         # API routes (/api/*)
│   └── web.php                         # Web routes
│
├── 📂 storage/logs/                    # Log files
│
└── 📂 vendor/                          # PHP packages
```

---

## 🎓 Hướng Dẫn Chi Tiết

### 1. 📂 Tạo Model (kế thừa BaseModel)

```php
// app/Models/Product.php

namespace App\Models;

class Product extends BaseModel
{
    protected $fillable = ['name', 'price', 'description', 'category_id', 'status'];

    // Override để custom search fields
    protected function getSearchableFields(): array
    {
        return ['name', 'description'];
    }

    // Quan hệ với Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

### 2. 📂 Tạo Repository

```php
// app/Repositories/ProductRepository.php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    // Override để custom search fields
    protected function getSearchableFields(): array
    {
        return ['name', 'description'];
    }
}
```

### 3. 📂 Tạo Service

```php
// app/Services/ProductService.php

namespace App\Services;

use App\Repositories\ProductRepository;

class ProductService extends BaseService
{
    public function __construct(ProductRepository $repository)
    {
        parent::__construct($repository);
    }

    // Hook trước khi tạo
    protected function beforeCreate(array $data): array
    {
        $data['status'] = 1; // Default active
        return $data;
    }
}
```

### 4. 📂 Tạo API Controller

```php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Services\ProductService;
use App\Http\Requests\PaginationRequest;

class ProductController extends BaseApiController
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index(PaginationRequest $request)
    {
        $data = $this->service->paginate(
            $request->getPerPage(),
            $request->getFilters(),
            $request->getSortParams()
        );
        return $this->paginatedResponse($data);
    }

    public function show(int $id)
    {
        $product = $this->service->findById($id);
        return $this->successResponse($product);
    }

    public function store(Request $request)
    {
        $product = $this->service->create($request->all());
        return $this->createdResponse($product);
    }

    public function update(Request $request, int $id)
    {
        $product = $this->service->update($id, $request->all());
        return $this->successResponse($product);
    }

    public function destroy(int $id)
    {
        $this->service->delete($id);
        return $this->successResponse(null, 'Xóa thành công');
    }
}
```

### 5. 🛣️ Định nghĩa API Routes

```php
// routes/api.php

use App\Http\Controllers\Api\ProductController;

Route::apiResource('products', ProductController::class);
```

### 3. � Tạo file HTML

```html
<!-- public/products.html -->
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh sách sản phẩm - TechZone</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Danh sách sản phẩm</h1>
    
    <!-- Container để hiển thị sản phẩm -->
    <div id="products-container"></div>

    <script src="js/api.js"></script>
    <script src="js/app.js"></script>
</body>
</html>
```

### 4. ⚡ Gọi API bằng JavaScript

```javascript
// public/js/api.js

const API_BASE_URL = 'http://localhost:8000/api';

// Hàm gọi API lấy danh sách sản phẩm
async function getProducts() {
    try {
        const response = await fetch(`${API_BASE_URL}/products`);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Lỗi:', error);
    }
}

// Hàm gọi API lấy 1 sản phẩm
async function getProduct(id) {
    const response = await fetch(`${API_BASE_URL}/products/${id}`);
    return await response.json();
}

// Hàm tạo sản phẩm mới
async function createProduct(productData) {
    const response = await fetch(`${API_BASE_URL}/products`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(productData)
    });
    return await response.json();
}
```

```javascript
// public/js/app.js

// Hiển thị danh sách sản phẩm khi trang load
document.addEventListener('DOMContentLoaded', async () => {
    const products = await getProducts();
    const container = document.getElementById('products-container');
    
    products.forEach(product => {
        container.innerHTML += `
            <div class="product-card">
                <img src="${product.image}" alt="${product.name}">
                <h3>${product.name}</h3>
                <p class="price">${product.price.toLocaleString()}đ</p>
                <button onclick="addToCart(${product.id})">
                    Thêm vào giỏ
                </button>
            </div>
        `;
    });
});
```

### 5. 🗄️ Tạo Model và Migration

```bash
# Tạo Model Product kèm Migration
php artisan make:model Product -m
```

```php
// database/migrations/xxxx_create_products_table.php

public function up()
{
    Schema::create('products', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->text('description')->nullable();
        $table->decimal('price', 10, 2);
        $table->string('image')->nullable();
        $table->integer('quantity')->default(0);
        $table->foreignId('category_id')->constrained();
        $table->timestamps();
    });
}
```

```php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'image', 'quantity', 'category_id'
    ];

    // Quan hệ với Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
```

---

## ⌨️ Các Lệnh Thường Dùng

### Artisan Commands (Laravel)
```bash
# Chạy server
php artisan serve

# Tạo Controller cho API
php artisan make:controller Api/ProductController --api

# Tạo Model + Migration
php artisan make:model Product -m

# Chạy migration
php artisan migrate

# Rollback migration
php artisan migrate:rollback

# Reset database (xóa hết data)
php artisan migrate:fresh

# Chạy seeder
php artisan db:seed

# Xem danh sách routes
php artisan route:list

# Xóa cache
php artisan cache:clear
php artisan config:clear
```

---

## 📚 Tài Liệu Tham Khảo

| Chủ đề | Link |
|--------|------|
| Laravel Docs | [laravel.com/docs](https://laravel.com/docs) |
| Laravel API Tutorial | [Laravel Bootcamp](https://bootcamp.laravel.com) |
| JavaScript Fetch API | [MDN Web Docs](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API) |
| REST API Design | [RESTful API Guidelines](https://restfulapi.net/) |

---

## � Ghi Chú Quan Trọng

### ⚠️ Lưu ý khi làm việc:
- **Không commit file `.env`** - chứa thông tin nhạy cảm
- **Không sửa trong `vendor/`** - sẽ bị ghi đè khi cài lại
- **Xem log lỗi** tại `storage/logs/laravel.log`
- **Test API** bằng Postman hoặc Thunder Client (VS Code extension)

### 🔧 Xử lý lỗi CORS:
Nếu gặp lỗi CORS khi gọi API từ HTML, sửa file `config/cors.php`:
```php
'paths' => ['api/*'],
'allowed_origins' => ['*'],
```

---

## 📄 License

Dự án phục vụ mục đích học tập.

---

<p align="center">
  <b>Made with ❤️ by TechZone Team</b>
</p>
