<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', function () {
        return ApiResponse::success(
            data: [
                'status' => 'healthy',
            ],
            message: 'Commerce Platform API is running.'
        );
    });
});