<?php

use Illuminate\Support\Facades\Route;

/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    php artisan route:clear
    php artisan route:list
*/



/************************************************************** Login *************************************************************/
Route::get('/connected', function () { return view("api-connected");})->name('api-connected');
Route::get('/notConnected', function () { return view("api-not-connected");})->name('api-not-connected');
Route::get('/getUserData', [\App\Http\Controllers\AuthController::class, 'getUserData'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**********************************************************************************************************************************/


/************************************************************** Notifications *************************************************************/
Route::get('/getNotifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/


/************************************************************** Planning *************************************************************/
Route::get('/getPlanning', [\App\Http\Controllers\PlanningController::class, 'getPlanning'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/


/************************************************************** Anecdotes *************************************************************/
Route::post('/getAnecdotes', [\App\Http\Controllers\AnecdoteController::class, 'getAnecdotes'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/likeAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'likeAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/warnAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'warnAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/sendAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'sendAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/deleteAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'deleteAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/


/*************************************************************** Skinder **************************************************************/
Route::get('/getProfilSkinder', [\App\Http\Controllers\SkinderController::class, 'getProfilSkinder'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/likeSkinder', [\App\Http\Controllers\SkinderController::class, 'likeSkinder'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::get('/getMySkinderMatches', [\App\Http\Controllers\SkinderController::class, 'getMySkinderMatches'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::get('/getMyProfilSkinder', [\App\Http\Controllers\SkinderController::class, 'getMyProfilSkinder'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/modifyProfilSkinder', [\App\Http\Controllers\SkinderController::class, 'modifyProfil'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/uploadRoomImage', [\App\Http\Controllers\SkinderController::class, 'uploadRoomImage'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/


require __DIR__.'/auth.php';