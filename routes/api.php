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

        // Knowledge API endpoints (protected) - Single action controllers
        Route::get('/knowledge', App\Http\Controllers\Knowledge\IndexController::class);
        Route::post('/knowledge', App\Http\Controllers\Knowledge\StoreController::class);
        Route::get('/knowledge/{knowledge}', App\Http\Controllers\Knowledge\ShowController::class)->middleware('can:view,knowledge');
        Route::put('/knowledge/{knowledge}', App\Http\Controllers\Knowledge\UpdateController::class)->middleware('can:update,knowledge');
        Route::delete('/knowledge/{knowledge}', App\Http\Controllers\Knowledge\DestroyController::class)->middleware('can:delete,knowledge');

        // Tag management endpoints
        Route::get('/tags', App\Http\Controllers\TagController::class);

        // Search endpoint
        Route::get('/search/knowledge', App\Http\Controllers\Knowledge\SearchController::class);
    });
});
