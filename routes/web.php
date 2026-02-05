<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Client\HomeController;
use App\Http\Controllers\Admin\ProductController;

// --- ROUTES CLIENT ---
Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/login', function () {
    return view('client.auth.login');
})->name('login');

Route::get('/register', function () {
    return view('client.auth.register');
})->name('register');


// --- ROUTES ADMIN ---
Route::prefix('admin')->group(function () {
    
    Route::get('/login', function () {
        return view('admin.auth.login');
    })->name('admin.login');

    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    // --- QUẢN LÝ SẢN PHẨM ---
    Route::get('/products', [ProductController::class, 'index'])->name('admin.products.index');
    Route::post('/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::post('/products/{id}', [ProductController::class, 'update'])->name('admin.products.update'); // Dùng POST cho update có file
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
});