<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


Route::controller(AuthController::class)
    ->prefix('auth')
    ->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');

        Route::post('/logout', 'logout')->middleware('auth:api');
    });

Route::middleware('auth:api')->group(function () {
    Route::controller(UserController::class)
        ->prefix('users')
        ->group(function () {
            Route::post('/', 'store');
            Route::get('/', 'index');
            Route::get('/{id}', 'show');
            Route::put('/{id}', 'update');
            Route::delete('/{id}', 'destroy');
        });
});

Route::middleware('auth:api')->controller(ProductController::class)
    ->prefix('products')
    ->group(function () {
        Route::post('/', 'store');
        Route::get('/', 'index');
        Route::get('/{id}', 'show');
        Route::put('/{id}', 'update');
        Route::delete('/{id}', 'destroy');
    });