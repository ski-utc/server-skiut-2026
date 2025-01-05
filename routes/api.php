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

/************************************************************** Home *************************************************************/
Route::get('/random-data', [\App\Http\Controllers\HomeController::class, 'getRandomData'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Notifications *************************************************************/
Route::get('/getNotifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/


/************************************************************** Planning *************************************************************/
Route::get('/getPlanning', [\App\Http\Controllers\PlanningController::class, 'getPlanning'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Défis *************************************************************/
Route::get('/challenges', [\App\Http\Controllers\DefisController::class, 'getChallenges']);
Route::post('/proofs', [\App\Http\Controllers\DefisController::class, 'postProof']);
Route::post('/proofs/{proofId}/validate', [\App\Http\Controllers\DefisController::class, 'validateProof']);
Route::post('/challenges/import', [\App\Http\Controllers\DefisController::class, 'importChallenges']);
Route::get('/proofs/validate', [\App\Http\Controllers\DefisController::class, 'getProofsForValidation']);
/**************************************************************************************************************************************/

/************************************************************** Anecdotes *************************************************************/
Route::post('/getAnecdotes', [\App\Http\Controllers\AnecdoteController::class, 'getAnecdotes'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/likeAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'likeAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/warnAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'warnAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/sendAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'sendAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
Route::post('/deleteAnecdote', [\App\Http\Controllers\AnecdoteController::class, 'deleteAnecdote'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Navettes *************************************************************/
Route::get('/getNavettes', [\App\Http\Controllers\NavetteController::class, 'index'])->middleware(\App\Http\Middleware\EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Administration *************************************************************/
Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'getAdmin'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getAdminChallenges', [\App\Http\Controllers\AdminController::class, 'getAdminChallenges'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getChallengeDetails/{challengeId}', [\App\Http\Controllers\AdminController::class, 'getChallengeDetails'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::post('/updateChallengeStatus/{challengeId}/{isValid}', [\App\Http\Controllers\AdminController::class, 'updateChallengeStatus'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getAdminAnecdotes', [\App\Http\Controllers\AdminController::class, 'getAdminAnecdotes'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getAnecdoteDetails/{anecdoteId}', [\App\Http\Controllers\AdminController::class, 'getAnecdoteDetails'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::post('/updateAnecdoteStatus/{anecdoteId}/{isValid}', [\App\Http\Controllers\AdminController::class, 'updateAnecdoteStatus'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getAdminNotifications', [\App\Http\Controllers\AdminController::class, 'getAdminNotifications'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::get('/getNotificationDetails/{notificationId}', [\App\Http\Controllers\AdminController::class, 'getNotificationDetails'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::post('/sendGeneralNotification', [\App\Http\Controllers\AdminController::class, 'sendGeneralNotification'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);
Route::post('/sendIndividualNotification/{userId}', [\App\Http\Controllers\AdminController::class, 'sendSpecificNotification'])->middleware([\App\Http\Middleware\EnsureTokenIsValid::class]);

/**************************************************************************************************************************************/

require __DIR__.'/auth.php';
