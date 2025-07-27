<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'Know API',
        'version' => '1.0.0',
        'status' => 'operational',
        'endpoints' => [
            'auth' => '/api/v1/auth/login',
            'knowledge' => '/api/v1/knowledge',
            'search' => '/api/v1/search/knowledge',
            'tags' => '/api/v1/tags',
            'health' => '/health',
        ],
        'documentation' => 'https://github.com/jordanpartridge/know-api',
    ]);
});
