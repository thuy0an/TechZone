<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Storefront\Auth\AuthController;
use App\Http\Controllers\Api\Storefront\ProductController;
use App\Http\Controllers\Api\Storefront\CartController;
use App\Http\Controllers\Api\Storefront\OrderController;

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

// Nhóm Public (Không cần đăng nhập)
Route::prefix('storefront')->group(function () {
    // HIỂN THỊ SẢN PHẨM 
    Route::get('/products', [ProductController::class, 'index']); // Danh sách SP
    Route::get('/products/{id}', [ProductController::class, 'show']); // Chi tiết SP
    Route::get('/categories', [ProductController::class, 'categories']); // Danh sách danh mục

    // ĐĂNG NHẬP,ĐĂNG KÝ
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Nhóm cần đăng nhập
Route::prefix('storefront')->middleware('auth:sanctum')->group(function () {
    // GIỎ HÀNG
    Route::get('/cart', [CartController::class, 'index']); // xem giỏ hàng
    Route::post('/cart/add', [CartController::class, 'add']); // thêm vào giỏ
    Route::delete('/cart/delete/{cartItemId}', [CartController::class, 'delete']); // xóa khỏi giỏ`

    // ĐẶT HÀNG (CHECKOUT)
    Route::post('/checkout', [OrderController::class, 'checkout']); // chốt đơn
    Route::get('/orders', [OrderController::class, 'myOrders']); // lịch sử đơn hàng

    // ĐĂNG XUẤT
    Route::post('/logout', [AuthController::class, 'logout']);
});





/*
|--------------------------------------------------------------------------
| Product Routes (Ví dụ)
|--------------------------------------------------------------------------
*/
// Route::apiResource('products', ProductController::class);
// Route::apiResource('categories', CategoryController::class);
// Route::apiResource('orders', OrderController::class);
