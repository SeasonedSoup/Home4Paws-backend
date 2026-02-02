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

// Protected routes (require token)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);

    Route::post('/paws', [PawsController::class, 'store']);
    Route::post('/paws/{id}/like', [PawsController::class, 'like']);
    Route::delete('/paws/{id}/like', [PawsController::class, 'unlike']);
    Route::patch('/paws/{id}/adopt', [PawsController::class, 'markAdopted']);
    Route::delete('/paws/{id}', [PawsController::class, 'destroy']);
});