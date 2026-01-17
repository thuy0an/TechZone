<?php

use Illuminate\Http\Request;
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

/*
|--------------------------------------------------------------------------
| Product Routes (Ví dụ)
|--------------------------------------------------------------------------
*/
// Route::apiResource('products', ProductController::class);
// Route::apiResource('categories', CategoryController::class);
// Route::apiResource('orders', OrderController::class);
