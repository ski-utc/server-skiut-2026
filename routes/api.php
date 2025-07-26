<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureTokenIsValid;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\DefisController;
use App\Http\Controllers\AnecdoteController;
use App\Http\Controllers\SkinderController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserPerformanceController;
use App\Http\Controllers\ClassementController;

/*
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas :
    php artisan route:clear
    php artisan route:list
*/

/************************************************************** Login *************************************************************/
Route::get('/connected', function () { return view("api-connected");})->name('api-connected');
Route::get('/notConnected', function () { return view("api-not-connected");})->name('api-not-connected');
Route::get('/getUserData', [\App\Http\Controllers\AuthController::class, 'getUserData'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Home *************************************************************/
Route::get('/getRandomData', [\App\Http\Controllers\HomeController::class, 'getRandomData'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Notifications *************************************************************/
Route::get('/getNotifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Planning *************************************************************/
Route::get('/getPlanning', [\App\Http\Controllers\PlanningController::class, 'getPlanning'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Défis *************************************************************/
Route::get('/challenges', [DefisController::class, 'getChallenges'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/getProofImage', [DefisController::class, 'getProofImage'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/uploadProofImage', [DefisController::class, 'uploadProofImage'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/deleteproofImage', [DefisController::class, 'deleteproofImage'])->middleware(EnsureTokenIsValid::class);
Route::get('/classement-chambres', [ClassementController::class, 'classementChambres'])->middleware(EnsureTokenIsValid::class);;
/**************************************************************************************************************************************/

/************************************************************** Anecdotes *************************************************************/
Route::post('/getAnecdotes', [AnecdoteController::class, 'getAnecdotes'])->middleware(EnsureTokenIsValid::class);
Route::post('/likeAnecdote', [AnecdoteController::class, 'likeAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/warnAnecdote', [AnecdoteController::class, 'warnAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/sendAnecdote', [AnecdoteController::class, 'sendAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/deleteAnecdote', [AnecdoteController::class, 'deleteAnecdote'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Navettes *************************************************************/
Route::get('/getNavettes', [\App\Http\Controllers\NavetteController::class, 'getNavettes'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/*************************************************************** Skinder **************************************************************/
Route::get('/getProfilSkinder', [SkinderController::class, 'getProfilSkinder'])->middleware(EnsureTokenIsValid::class);
Route::post('/likeSkinder', [SkinderController::class, 'likeSkinder'])->middleware(EnsureTokenIsValid::class);
Route::get('/getMySkinderMatches', [SkinderController::class, 'getMySkinderMatches'])->middleware(EnsureTokenIsValid::class);
Route::get('/getMyProfilSkinder', [SkinderController::class, 'getMyProfilSkinder'])->middleware(EnsureTokenIsValid::class);
Route::post('/modifyProfilSkinder', [SkinderController::class, 'modifyProfil'])->middleware(EnsureTokenIsValid::class);
Route::post('/uploadRoomImage', [SkinderController::class, 'uploadRoomImage'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Administration *************************************************************/
Route::get('/admin', [AdminController::class, 'getAdmin'])->middleware([EnsureTokenIsValid::class]);

Route::get('/getAdminChallenges', [AdminController::class, 'getAdminChallenges'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/getChallengeDetails/{challengeId}', [AdminController::class, 'getChallengeDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/updateChallengeStatus/{challengeId}/{isValid}/{isDelete}', [AdminController::class, 'updateChallengeStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/getAdminAnecdotes', [AdminController::class, 'getAdminAnecdotes'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/getAnecdoteDetails/{anecdoteId}', [AdminController::class, 'getAnecdoteDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/updateAnecdoteStatus/{anecdoteId}/{isValid}', [AdminController::class, 'updateAnecdoteStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/getAdminNotifications', [AdminController::class, 'getAdminNotifications'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/getNotificationDetails/{notificationId}', [AdminController::class, 'getNotificationDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/deleteNotification/{userId}/{delete}', [AdminController::class, 'deleteNotification'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/sendNotification', [AdminController::class, 'sendNotificationToAll'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/sendIndividualNotification/{userId}', [AdminController::class, 'sendIndividualNotification'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/getMaxFileSize', [\App\Http\Controllers\UserController::class, 'getMaxFileSize'])->middleware([EnsureTokenIsValid::class]);
Route::post('/save-token', [\App\Http\Controllers\UserController::class, 'saveToken'])->middleware([EnsureTokenIsValid::class]);
/*********************************************************************************************************************************************/

/************************************************************** Vitesse de glisse *************************************************************/
Route::post('/update-performance', [UserPerformanceController::class, 'updatePerformance'])->middleware(EnsureTokenIsValid::class);
Route::get('/classement-performances', [ClassementController::class, 'classementPerformances'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************************/

/************************************************************** RGPD *************************************************************/
Route::post('/rgpd/anonymize-my-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeMyData'])->middleware(EnsureTokenIsValid::class);
Route::post('/rgpd/delete-my-data', [\App\Http\Controllers\RgpdController::class, 'deleteMyData'])->middleware(EnsureTokenIsValid::class);
Route::get('/rgpd/export-my-data', [\App\Http\Controllers\RgpdController::class, 'exportMyData'])->middleware(EnsureTokenIsValid::class);
Route::post('/rgpd/anonymize-all-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeAllData']);
Route::post('/rgpd/delete-all-data', [\App\Http\Controllers\RgpdController::class, 'deleteAllData']);
/**********************************************************************************************************************************************/
