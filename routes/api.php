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

        // Knowledge API endpoints (protected)
        Route::apiResource('knowledge', App\Http\Controllers\KnowledgeController::class);

        // Tag management endpoints
        Route::get('/tags', function () {
            return \App\Http\Resources\TagResource::collection(\App\Models\Tag::all());
        });

        // Search endpoint
        Route::get('/search/knowledge', function (\Illuminate\Http\Request $request) {
            $query = \App\Models\Knowledge::with(['user', 'gitContext', 'tags']);

            if ($request->has('q')) {
                $query->search($request->q);
            }

            // Can search public knowledge or user's own knowledge
            $query->where(function ($q) use ($request) {
                $q->where('is_public', true)
                    ->orWhere('user_id', $request->user()->id);
            });

            $results = $query->latest()->paginate(10);

            return \App\Http\Resources\KnowledgeResource::collection($results);
        });
    });
});
