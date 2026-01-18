<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

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

// Route lấy thông tin user đang đăng nhập (cần auth)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// ----- AUTH ROUTES -----

// Nhóm Public (Không cần đăng nhập)
Route::prefix('auth')->group(function () {
    // Khách hàng
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    // Nhân viên
    Route::post('admin/login', [AuthController::class, 'adminLogin']);
});

// Nhóm Private (Phải có Token mới vào được)
// Dùng middleware 'auth:sanctum' để kiểm tra token
Route::middleware('auth:sanctum')->group(function () {
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::get('auth/profile', [AuthController::class, 'profile']);
});

/*
|--------------------------------------------------------------------------
| Product Routes (Ví dụ)
|--------------------------------------------------------------------------
*/
// Route::apiResource('products', ProductController::class);
// Route::apiResource('categories', CategoryController::class);
// Route::apiResource('orders', OrderController::class);
