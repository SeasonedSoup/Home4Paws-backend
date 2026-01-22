<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PawsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. 
| Routes are assigned to the "api" middleware group.
|
*/

/*
|------------------------------------------------------------------
| Public Routes
|------------------------------------------------------------------
*/
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);
Route::get('/paws', [PawsController::class, 'index']); // public listing of posts

/*
|------------------------------------------------------------------
| Protected Routes (auth:sanctum)
|------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::post('/logout', [UserController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'profile']);

    // PAWS posts
    Route::post('/paws', [PawsController::class, 'store']);
});

Route::middleware('auth:sanctum')->patch('/paws/{id}/adopted', [PawsController::class, 'markAdopted']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/paws/{id}/like', [PawsController::class, 'like']);
    Route::delete('/paws/{id}/like', [PawsController::class, 'unlike']);
});
