<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::controller(UserController::class)
    ->prefix('users')
    ->group(function () {
        Route::post('/', 'store');       
        Route::get('/', 'index');       
        Route::get('/{id}', 'show');      
        Route::put('/{id}', 'update');    
        Route::delete('/{id}', 'destroy'); 
    });