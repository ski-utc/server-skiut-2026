<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| This file contains routes related to authetification, including login,
| and logout. These routes to corresponding controller methods that handle
| the logicfor each action.
|
*/
Route::prefix('/auth')->name('auth.')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])
        ->name('login');

    Route::get('/callback', [AuthController::class, 'callback'])
        ->name('callback');

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])
            ->name('logout');
    });
});
