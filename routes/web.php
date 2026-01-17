<?php

use Illuminate\Support\Facades\Route;

// Redirect trang chủ đến index.html
Route::get('/', function () {
    return redirect('/index.html');
});
