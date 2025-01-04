<?php

use Illuminate\Support\Facades\Route;

/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    php artisan route:clear
    php artisan route:list
*/



/************************************************************** Login *************************************************************/
Route::get('/connected', function () { return view("api-connected");})->name('api-connected');
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

/************************************************************** Administration *************************************************************/
Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'getAdmin'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getAdminChallenges', [\App\Http\Controllers\AdminController::class, 'getAdminChallenges'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);;
Route::get('/getAdminAnecdotes', [\App\Http\Controllers\AdminController::class, 'getAdminAnecdotes'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);;
Route::get('/getAnecdoteDetails/{anecdoteId}', [\App\Http\Controllers\AdminController::class, 'getAnecdoteDetails'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::post('/updateAnecdoteStatus/{id}/{valid}', [\App\Http\Controllers\AdminController::class, 'updateAnecdoteStatus'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);;
/**************************************************************************************************************************************/

require __DIR__.'/auth.php';