<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes for Knowledge Management
Route::prefix('v1')->group(function () {
    // Authentication routes
    Route::post('/auth/register', function () {
        return response()->json(['message' => 'Registration endpoint']);
    });
    
    Route::post('/auth/login', function () {
        return response()->json(['message' => 'Login endpoint']);
    });
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', function () {
            return response()->json(['message' => 'Logout endpoint']);
        });
        
        // Knowledge API endpoints (protected) - TODO: Create KnowledgeController
        // Route::apiResource('knowledge', App\Http\Controllers\KnowledgeController::class);
    });
});