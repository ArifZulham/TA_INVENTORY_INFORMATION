<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — dilindungi Sanctum (token Bearer atau cookie SPA)
|--------------------------------------------------------------------------
*/

// Auth publik (tanpa token)
Route::post('/login', [AuthController::class, 'apiLogin']);
Route::post('/register', [AuthController::class, 'apiRegister']);

// Endpoint terproteksi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'apiLogout']);

    // Pencarian produk berbasis AI
    Route::post('/products/search', [ProductController::class, 'searchProduct']);
});
