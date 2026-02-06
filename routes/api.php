<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\PawsController;
use App\Http\Controllers\InboxController;

use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/hi', function () {
    return response()->json(['message' => 'Welcome to Home4Paws API']);
});

Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);


// Public paws routes (anyone can view)
Route::get('/paws/global-stats', [PawsController::class, 'getGlobalStats']);
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

    Route::get('/inbox', [InboxController::class, 'index']);
    
    // Get only NEW (unread) items
    Route::get('/inbox/unread', [InboxController::class, 'unreadOnly']);

    // Mark one item as read (Frontend must call this when the user clicks an item)
    Route::post('/inbox/{id}/mark-read', [InboxController::class, 'markAsRead']);
    
    // Get just the count for the bell icon
    Route::get('/inbox/unread-count', [InboxController::class, 'unreadCount']);

    Route::post('/paws/{id}/visitFacebookAcc', [App\Http\Controllers\PawsController::class, 'logFacebookClick']);
});