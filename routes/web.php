<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Customer\ProfileController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->name('google.callback');

Route::middleware('auth')->group(function () {
    Route::get('/account/profile', [ProfileController::class, 'show'])
        ->name('customer.profile');

    Route::put('/account/profile', [ProfileController::class, 'update'])
        ->name('customer.profile.update');
    
    Route::put('/account/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('customer.profile.password.update');
});