<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Customer\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AddressController;

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

Route::middleware('auth')->prefix('account')->group(function () {
    Route::get('/addresses', [AddressController::class, 'index'])
        ->name('customer.addresses.index');

    Route::get('/addresses/create', [AddressController::class, 'create'])
        ->name('customer.addresses.create');

    Route::post('/addresses', [AddressController::class, 'store'])
        ->name('customer.addresses.store');

    Route::get('/addresses/{address}/edit', [AddressController::class, 'edit'])
        ->name('customer.addresses.edit');

    Route::put('/addresses/{address}', [AddressController::class, 'update'])
        ->name('customer.addresses.update');

    Route::delete('/addresses/{address}', [AddressController::class, 'destroy'])
        ->name('customer.addresses.destroy');
});