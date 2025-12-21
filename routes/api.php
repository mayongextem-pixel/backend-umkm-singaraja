<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes - Singaraja UMKM
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. RUTE PUBLIK (Bisa diakses siapa saja)
// ==========================================

// Login untuk Admin mendapatkan Token
Route::post('/login', [AuthController::class, 'login']);

// Ambil semua produk untuk Katalog dan Home
Route::get('/produk', [ProdukController::class, 'index']);

// Ambil detail satu produk untuk halaman Detail
Route::get('/produk/{id}', [ProdukController::class, 'show']);

// Form Pendaftaran UMKM (Boleh diakses publik agar warga bisa daftar)
Route::post('/produk', [ProdukController::class, 'store']);


// ==========================================
// 2. RUTE TERPROTEKSI (Wajib Login/Token)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {
    
    // Ambil data profil admin yang sedang login
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Fitur Admin: Hapus UMKM dari Dashboard
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);
    
    // Fitur Admin: Update data UMKM (Jika Anda ingin buat fitur edit nantinya)
    // Route::post('/produk/{id}', [ProdukController::class, 'update']); 

    // Logout untuk menghapus token akses
    Route::post('/logout', [AuthController::class, 'logout']);
});