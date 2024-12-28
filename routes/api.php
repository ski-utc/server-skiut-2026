<?php

use Illuminate\Support\Facades\Route;

/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    php artisan route:clear
    php artisan route:list
*/



/************************************************************** Login *************************************************************/
Route::get('/connected', function () { return view("api-connected");})->middleware(\App\Http\Middleware\EnsureTokenIsValid::class)->name('api-connected');
Route::get('/getUserData', [\App\Http\Controllers\AuthController::class, 'getUserData'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**********************************************************************************************************************************/


/************************************************************** Anecdotes *************************************************************/
Route::post('/getAnecdotes', [\App\Http\Controllers\AnecdoteController::class, 'getAnecdotes'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/likeAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'likeAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/warnAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'warnAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/sendAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'sendAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/


require __DIR__.'/auth.php';