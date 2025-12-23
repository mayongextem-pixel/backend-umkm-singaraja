<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes - Singaraja UMKM with Approval Workflow
|--------------------------------------------------------------------------
*/

// ==========================================
// 1. PUBLIC ROUTES (Anyone can access)
// ==========================================

// Authentication
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Public katalog - only APPROVED UMKM
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{id}', [ProdukController::class, 'show']);


// ==========================================
// 2. PROTECTED ROUTES (Require authentication)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {

    // User profile
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // --------------------------------------
    // UMKM User Routes (Submit & manage own UMKM)
    // --------------------------------------

    // Create new UMKM (status will be 'pending' for users, 'approved' for admin)
    Route::post('/produk', [ProdukController::class, 'store']);

    // Get my own submissions
    Route::get('/my-umkm', [ProdukController::class, 'mySubmissions']);

    // Update own UMKM (users can only edit pending, admin can edit any)
    Route::put('/produk/{id}', [ProdukController::class, 'update']);
    Route::patch('/produk/{id}', [ProdukController::class, 'update']);

    // Delete own UMKM (or admin can delete any)
    Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);

    // --------------------------------------
    // Admin Only Routes
    // --------------------------------------

    // Get ALL UMKM regardless of status (admin only)
    Route::get('/admin/produk', [ProdukController::class, 'indexAll']);

    // Approve UMKM (admin only)
    Route::post('/produk/{id}/approve', [ProdukController::class, 'approve']);

    // Reject UMKM (admin only)
    Route::post('/produk/{id}/reject', [ProdukController::class, 'reject']);
});