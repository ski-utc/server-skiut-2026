<?php

use Illuminate\Support\Facades\Route;

/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    php artisan route:clear
    php artisan route:list
*/



/***************************************** Login *****************************************/
Route::get('/connected', function () { return view("api-connected");})->middleware(\App\Http\Middleware\EnsureTokenIsValid::class)->name('api-connected');
Route::get('/getUserData', [\App\Http\Controllers\AuthController::class, 'getUserData'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/*****************************************************************************************/



Route::get('/getAnecdotes', [\App\Http\Controllers\AnecdoteController::class, 'getAnecdotes']);



require __DIR__.'/auth.php';