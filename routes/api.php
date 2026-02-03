<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PawsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/hi', function () {
    return response()->json(['message' => 'Welcome to Home4Paws API']);
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// Public paws routes (anyone can view)
Route::get('/paws', [PawsController::class, 'index']);          // Get all posts
Route::get('/paws/{id}', [PawsController::class, 'show']);      // Get single post

// Protected routes (require token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);

    // Paws - authenticated actions
    Route::post('/paws', [PawsController::class, 'store']);           // Create post
    Route::put('/paws/{id}', [PawsController::class, 'update']);      // Update post
    Route::delete('/paws/{id}', [PawsController::class, 'destroy']);  // Delete post
    Route::post('/paws/{id}/like', [PawsController::class, 'like']);  // Like post
    Route::patch('/paws/{id}/adopt', [PawsController::class, 'markAdopted']); // Mark adopted
});