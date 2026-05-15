<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StorageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/change-password', 'changePassword')->middleware('auth:api');
        Route::post('/logout', 'logout')->middleware('auth:api');
        Route::get('/user', 'user')->middleware('auth:api');

    });




Route::middleware(['auth:api','role:admin'])->group(function () {
    Route::controller(UserController::class)
        ->prefix('users')
        ->group(function () {
            Route::post('/', 'store');
            Route::get('/', 'index');
            Route::get('/roles', 'roles');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
});

Route::middleware(['auth:api','role:admin|moderator|user'])->controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::post('/', 'store')->middleware(['role:admin|moderator']);
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update')->middleware(['role:admin|moderator']);
        Route::delete('/{id}', 'destroy')->middleware(['role:admin|moderator']);
    });

Route::get('/products', [ProductController::class, 'index']);

    Route::middleware('auth:api')
    ->prefix('bookings')
    ->controller(BookingController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::get('/my-bookings', 'myBookings');
        Route::middleware('role:admin|moderator')->group(function () {
            Route::get('/', 'index');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
    });

    Route::prefix('storage-service')
    ->controller(StorageController::class)
    ->group(function () {
        Route::get('/', 'logToS3');
        Route::get('/export-booking', 'exportBookingDataIntoXlsx');
        Route::get('/export-user', 'exportUsersIntoXlsx');
        Route::get('/export-product', 'exportProductsIntoXlsx');
        Route::post('/import-product', 'importProductsFromXlsxBucket');
        Route::post('/import-user', 'importUsersFromXlsxBucket');
    });