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
        // Authentication endpoints
        Route::prefix('auth')->group(function () {
            Route::post('/logout', App\Http\Controllers\Auth\LogoutController::class);
        });

        // Knowledge management endpoints
        Route::prefix('knowledge')->name('knowledge.')->group(function () {
            Route::get('/', App\Http\Controllers\Knowledge\IndexController::class)->name('index');
            Route::post('/', App\Http\Controllers\Knowledge\StoreController::class)->name('store');

            // Routes requiring ownership authorization
            Route::middleware('can:view,knowledge')->group(function () {
                Route::get('/{knowledge}', App\Http\Controllers\Knowledge\ShowController::class)->name('show');
            });

            Route::middleware('can:update,knowledge')->group(function () {
                Route::put('/{knowledge}', App\Http\Controllers\Knowledge\UpdateController::class)->name('update');
            });

            Route::middleware('can:delete,knowledge')->group(function () {
                Route::delete('/{knowledge}', App\Http\Controllers\Knowledge\DestroyController::class)->name('destroy');
            });
        });

        // Tag management
        Route::get('/tags', App\Http\Controllers\TagController::class)->name('tags.index');

        // Search endpoints
        Route::prefix('search')->name('search.')->group(function () {
            Route::get('/knowledge', App\Http\Controllers\Knowledge\SearchController::class)->name('knowledge');
        });
    });
});
