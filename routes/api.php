<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Storefront\Auth\AuthController;
use App\Http\Controllers\Api\Storefront\ProductController;
use App\Http\Controllers\Api\Storefront\BrandController as StorefrontBrandController;
use App\Http\Controllers\Api\Storefront\CartController;
use App\Http\Controllers\Api\Storefront\OrderController;
use App\Http\Controllers\Api\Storefront\AddressController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\BrandController;
use \App\Http\Controllers\Api\Admin\SupplierController;
use App\Http\Controllers\Api\Admin\Auth\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Api\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Api\Admin\ImportNoteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Tất cả routes ở đây sẽ có prefix /api
| Ví dụ: /api/products, /api/categories
|
*/

// Route test API
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API TechZone đang hoạt động!',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// ============================================
// STOREFRONT Routes
// Nhóm Public (Không cần đăng nhập)
// ============================================
Route::prefix('storefront')->group(function () {
    // HIỂN THỊ SẢN PHẨM 
    Route::get('/products', [ProductController::class, 'index']); // Danh sách SP
    Route::get('/products/search/basic', [ProductController::class, 'searchBasic']); // Tìm kiếm cơ bản
    Route::get('/products/search/advanced', [ProductController::class, 'searchAdvanced']); // Tìm kiếm nâng cao
    Route::get('/products/category/{category_id}', [ProductController::class, 'productsByCategory']); // SP theo danh mục
    Route::get('/products/{id}', [ProductController::class, 'show']); // Chi tiết SP
    Route::get('/categories', [ProductController::class, 'categories']); // Danh sách danh mục
    Route::get('/brands', [StorefrontBrandController::class, 'index']); // Danh sách thương hiệu

    // ĐĂNG NHẬP,ĐĂNG KÝ
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// ============================================
// CLIENT Routes (Alias cho storefront public)
// ============================================
Route::prefix('client')->group(function () {
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::get('/products/category/{category_id}', [ProductController::class, 'productsByCategory']);
});

// ============================================
// STOREFRONT Routes
// Nhóm cần đăng nhập
// ============================================
Route::prefix('storefront')->middleware('auth:sanctum')->group(function () {
    // GIỎ HÀNG
    Route::get('/cart', [CartController::class, 'index']); // xem giỏ hàng
    Route::post('/cart/add', [CartController::class, 'add']); // thêm vào giỏ
    Route::delete('/cart/delete/{cartItemId}', [CartController::class, 'delete']); // xóa khỏi giỏ`

    // ĐẶT HÀNG (CHECKOUT)
    Route::post('/checkout', [OrderController::class, 'checkout']); // chốt đơn
    Route::get('/orders', [OrderController::class, 'myOrders']); // lịch sử đơn hàng

    // ĐỊA CHỈ NHẬN HÀNG
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);

    // ĐĂNG XUẤT
    Route::post('/logout', [AuthController::class, 'logout']);
});


// ============================================
// ADMIN Routes
// Lưu ý: Chưa thêm middleware auth vì US-32 (Admin Auth) chưa hoàn thành
// Sẽ bổ sung ->middleware('auth:sanctum') sau
// ============================================
Route::prefix('admin')->group(function () {
    // API Public của Admin
    Route::post('/login', [AdminAuthController::class, 'login']);

    // Nhóm cần xác thực Token của Admin
    Route::middleware('auth:sanctum')->group(function () {
        // ĐĂNG XUẤT
        Route::post('/logout', [AdminAuthController::class, 'logout']);

        // QUẢN LÝ SẢN PHẨM
        Route::get('products/{id}/price-histories', [AdminProductController::class, 'priceHistories']);
        Route::apiResource('products', AdminProductController::class);

        Route::apiResource('categories', CategoryController::class);

        // QUẢN LÝ THƯƠNG HIỆU (US-18)
        Route::apiResource('brands', BrandController::class);

        // QUẢN LÝ ĐƠN HÀNG
        Route::get('/orders', [AdminOrderController::class, 'index']);
        Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
        Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);

        // QUẢN LÝ NHÀ CUNG CẤP
        Route::apiResource('suppliers', SupplierController::class);
        Route::get('suppliers/{id}/transaction-history', [SupplierController::class, 'transactionHistory']);

        // NHẬP KHO
        Route::put('import-notes/{id}/complete', [ImportNoteController::class, 'complete']);
        Route::apiResource('import-notes', ImportNoteController::class);
        Route::post('import-notes/{id}/pay', [\App\Http\Controllers\Api\Admin\ImportNoteController::class, 'pay']);
    });
});


/*
|--------------------------------------------------------------------------
| Product Routes (Ví dụ)
|--------------------------------------------------------------------------
*/
// Route::apiResource('products', ProductController::class);
// Route::apiResource('categories', CategoryController::class);
// Route::apiResource('orders', OrderController::class);
