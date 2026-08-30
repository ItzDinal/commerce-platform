<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Customer\ProfileController;
use App\Http\Controllers\Customer\AddressController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Customer\WishlistController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Admin\AuthController;

Route::view('/', 'welcome')->name('home');

Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->name('google.redirect');

Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->name('google.callback');

Route::middleware('auth')->group(function () {

    // Customer Dashboard
    Route::get('/account', [DashboardController::class, 'index'])
        ->name('customer.dashboard');

    // Customer Profile
    Route::get('/account/profile', [ProfileController::class, 'show'])
        ->name('customer.profile');

    Route::put('/account/profile', [ProfileController::class, 'update'])
        ->name('customer.profile.update');

    Route::put('/account/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('customer.profile.password.update');

    // Customer Wishlist
    Route::get('/account/wishlist', [WishlistController::class, 'index'])
        ->name('customer.wishlist.index');

    Route::post('/account/wishlist', [WishlistController::class, 'store'])
        ->name('customer.wishlist.store');

    Route::delete('/account/wishlist/{wishlistItem}', [WishlistController::class, 'destroy'])
        ->name('customer.wishlist.destroy');

    Route::post(
    '/account/wishlist/{wishlistItem}/move-to-cart',
    [WishlistController::class, 'moveToCart']
    )->name('customer.wishlist.move-to-cart');

    // Customer Addresses
    Route::get('/account/addresses', [AddressController::class, 'index'])
        ->name('customer.addresses.index');

    Route::get('/account/addresses/create', [AddressController::class, 'create'])
        ->name('customer.addresses.create');

    Route::post('/account/addresses', [AddressController::class, 'store'])
        ->name('customer.addresses.store');

    Route::get('/account/addresses/{address}/edit', [AddressController::class, 'edit'])
        ->name('customer.addresses.edit');

    Route::put('/account/addresses/{address}', [AddressController::class, 'update'])
        ->name('customer.addresses.update');

    Route::delete('/account/addresses/{address}', [AddressController::class, 'destroy'])
        ->name('customer.addresses.destroy');

    Route::put('/account/addresses/{address}/default-shipping', [AddressController::class, 'setDefaultShipping'])
        ->name('customer.addresses.default-shipping');

    Route::put('/account/addresses/{address}/default-billing', [AddressController::class, 'setDefaultBilling'])
        ->name('customer.addresses.default-billing');

    // Customer Cart
    Route::get('/account/cart', [CartController::class, 'index'])
        ->name('customer.cart.index');

    Route::post('/account/cart', [CartController::class, 'store'])
        ->name('customer.cart.store');

    

    // Admin Authentication
    Route::middleware('guest')->group(function () {
        Route::get('/admin/login', [AuthController::class, 'showLogin'])
            ->name('admin.login');

        Route::post('/admin/login', [AuthController::class, 'login'])
            ->name('admin.login.submit');
    });

    Route::post('/admin/logout', [AuthController::class, 'logout'])
        ->middleware('auth')
        ->name('admin.logout');
    });