<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', App\Http\Controllers\UserController::class);

// API Routes for Knowledge Management
Route::prefix('v1')->group(function () {
    // Authentication routes - with rate limiting
    Route::middleware('throttle:5,1')->group(function () {
        Route::post('/auth/register', App\Http\Controllers\Auth\RegisterController::class);
        Route::post('/auth/login', App\Http\Controllers\Auth\LoginController::class);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', App\Http\Controllers\Auth\LogoutController::class);

        // Knowledge API endpoints (protected) - TODO: Create KnowledgeController
        // Route::apiResource('knowledge', App\Http\Controllers\KnowledgeController::class);
    });
});
