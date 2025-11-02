<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnecdoteController;
use App\Http\Controllers\ClassementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DefisController;
use App\Http\Controllers\SkinderController;
use App\Http\Controllers\UserPerformanceController;
use App\Http\Controllers\MonoprutController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Support\Facades\Route;

/************************************************************** Login *************************************************************/
Route::get('/connected', function () { return view('api-connected');})->name('api-connected');
Route::get('/notConnected', function () { return view('api-not-connected');})->name('api-not-connected');
Route::get('/getUserData', [\App\Http\Controllers\AuthController::class, 'getUserData'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Home *************************************************************/
Route::get('/getRandomData', [\App\Http\Controllers\HomeController::class, 'getRandomData'])->middleware(EnsureTokenIsValid::class);
Route::get('/getWeather', [\App\Http\Controllers\HomeController::class, 'getWeather'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Notifications *************************************************************/
// Routes notifications pour utilisateurs
Route::middleware(EnsureTokenIsValid::class)->group(function () {
    Route::get('/getNotifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
});

// Routes notifications pour admin (nouvelles)
Route::middleware([EnsureTokenIsValid::class, AdminMiddleware::class])->group(function () {
    Route::post('/createNotification', [\App\Http\Controllers\NotificationController::class, 'createNotification']);
    Route::post('/notifications/{id}/toggle-display', [\App\Http\Controllers\NotificationController::class, 'toggleDisplay']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'deleteNotification']);
    Route::get('/getRecipientsData', [\App\Http\Controllers\NotificationController::class, 'getRecipientsData']);
});
/**************************************************************************************************************************************/

/************************************************************** Planning *************************************************************/
Route::get('/getPlanning', [\App\Http\Controllers\PlanningController::class, 'getPlanning'])->middleware(EnsureTokenIsValid::class);
Route::get('/getUserPermanences', [\App\Http\Controllers\PlanningController::class, 'getUserPermanences'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Défis *************************************************************/
Route::get('/challenges', [DefisController::class, 'getChallenges'])->middleware(EnsureTokenIsValid::class);
// Nouvelles routes pour support vidéo
Route::post('/challenges/getProofMedia', [DefisController::class, 'getProofMedia'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/uploadProofMedia', [DefisController::class, 'uploadProofMedia'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/deleteProofMedia', [DefisController::class, 'deleteProofMedia'])->middleware(EnsureTokenIsValid::class);
// Anciennes routes pour compatibilité
Route::post('/challenges/getProofImage', [DefisController::class, 'getProofMedia'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/uploadProofImage', [DefisController::class, 'uploadProofMedia'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/deleteproofImage', [DefisController::class, 'deleteProofMedia'])->middleware(EnsureTokenIsValid::class);

Route::get('/classement-chambres', [ClassementController::class, 'classementChambres'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Anecdotes *************************************************************/
Route::post('/getAnecdotes', [AnecdoteController::class, 'getAnecdotes'])->middleware(EnsureTokenIsValid::class);
Route::post('/likeAnecdote', [AnecdoteController::class, 'likeAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/warnAnecdote', [AnecdoteController::class, 'warnAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/sendAnecdote', [AnecdoteController::class, 'sendAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/deleteAnecdote', [AnecdoteController::class, 'deleteAnecdote'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Contacts *************************************************************/
Route::get('/getContacts', [ContactController::class, 'getContacts'])->middleware(EnsureTokenIsValid::class);
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
Route::get('/getRoomDetails/{roomId}', [SkinderController::class, 'getRoomDetails'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Administration *************************************************************/
Route::get('/admin', [AdminController::class, 'getAdmin'])->middleware([EnsureTokenIsValid::class]);

Route::get('/getAdminChallenges', [AdminController::class, 'getAdminChallenges'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/getChallengeDetails/{challengeId}', [AdminController::class, 'getChallengeDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/updateChallengeStatus/{challengeId}/{isValid}/{isDelete}', [AdminController::class, 'updateChallengeStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/getAdminAnecdotes', [AdminController::class, 'getAdminAnecdotes'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/getAnecdoteDetails/{anecdoteId}', [AdminController::class, 'getAnecdoteDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/updateAnecdoteStatus/{anecdoteId}/{isValid}', [AdminController::class, 'updateAnecdoteStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/getAdminNotifications', [\App\Http\Controllers\NotificationController::class, 'getAdminNotifications'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/getNotificationDetails/{notificationId}', [AdminController::class, 'getNotificationDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/displayNotification/{notificationId}/{display}', [AdminController::class, 'displayNotification'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/sendNotification', [AdminController::class, 'sendNotificationToAll'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/sendIndividualNotification/{userId}', [AdminController::class, 'sendIndividualNotification'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

// Routes pour la gestion des utilisateurs et membres
Route::get('/getUsers', [AdminController::class, 'getUsers'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/updateUserMemberStatus', [AdminController::class, 'updateUserMemberStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/updateUserStatus', [AdminController::class, 'updateUserStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/getMaxFileSize', [\App\Http\Controllers\UserController::class, 'getMaxFileSize'])->middleware([EnsureTokenIsValid::class]);
Route::post('/save-token', [\App\Http\Controllers\UserController::class, 'saveToken'])->middleware([EnsureTokenIsValid::class]);

// Routes pour les permanences (membres uniquement)
Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/permanences/my', [\App\Http\Controllers\PermanenceController::class, 'getUserPermanences']);
});

// Routes pour les permanences (admin uniquement)
Route::middleware([EnsureTokenIsValid::class, AdminMiddleware::class])->group(function () {
    Route::get('/permanences', [\App\Http\Controllers\PermanenceController::class, 'getAllPermanences']);
    Route::post('/permanences', [\App\Http\Controllers\PermanenceController::class, 'createPermanence']);
    Route::put('/permanences/{id}', [\App\Http\Controllers\PermanenceController::class, 'updatePermanence']);
    Route::delete('/permanences/{id}', [\App\Http\Controllers\PermanenceController::class, 'deletePermanence']);
    Route::get('/permanences/members', [\App\Http\Controllers\PermanenceController::class, 'getAssociationMembers']);
    Route::post('/permanences/send-reminders', [\App\Http\Controllers\PermanenceController::class, 'sendReminders']);
});

// Routes pour les tournées de chambres (membres uniquement)
Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/room-tours/my-tour', [\App\Http\Controllers\RoomTourController::class, 'getUserTour']);
    Route::get('/room-tours/status', [\App\Http\Controllers\RoomTourController::class, 'getTourStatusForTraveler']);
    Route::post('/room-tours/visits/{visitId}/mark-visited', [\App\Http\Controllers\RoomTourController::class, 'markRoomVisited']);
    Route::post('/room-tours/visits/{visitId}/unmark-visited', [\App\Http\Controllers\RoomTourController::class, 'unmarkVisited']);
    Route::post('/room-tours/my-tour/reorder', [\App\Http\Controllers\RoomTourController::class, 'reorderRooms']);
});

// Routes pour les tournées de chambres (admin uniquement)
Route::middleware([EnsureTokenIsValid::class, AdminMiddleware::class])->group(function () {
    Route::get('/room-tours', [\App\Http\Controllers\RoomTourController::class, 'getAllTours']);
    Route::post('/room-tours', [\App\Http\Controllers\RoomTourController::class, 'createTour']);
    Route::post('/room-tours/{tourId}/toggle', [\App\Http\Controllers\RoomTourController::class, 'toggleTour']);
    Route::delete('/room-tours/{tourId}', [\App\Http\Controllers\RoomTourController::class, 'deleteTour']);
    Route::get('/room-tours/available-rooms', [\App\Http\Controllers\RoomTourController::class, 'getAvailableRooms']);
});
/*********************************************************************************************************************************************/

/************************************************************** Vitesse de glisse *************************************************************/
Route::post('/update-performance', [UserPerformanceController::class, 'updatePerformance'])->middleware(EnsureTokenIsValid::class);
Route::get('/user-performances', [UserPerformanceController::class, 'getUserPerformances'])->middleware(EnsureTokenIsValid::class);
Route::post('/delete-performance-session', [UserPerformanceController::class, 'deletePerformanceSession'])->middleware(EnsureTokenIsValid::class);
Route::get('/classement-performances', [ClassementController::class, 'classementPerformances'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************************/

/************************************************************** Monoprut *************************************************************/
Route::get('/getArticles', [MonoprutController::class, 'getArticles'])->middleware(EnsureTokenIsValid::class);
Route::post('/createArticle', [MonoprutController::class, 'createArticle'])->middleware(EnsureTokenIsValid::class);
Route::post('/shotgunArticle', [MonoprutController::class, 'shotgunArticle'])->middleware(EnsureTokenIsValid::class);
Route::get('/myGivenArticles', [MonoprutController::class, 'myGivenArticles'])->middleware(EnsureTokenIsValid::class);
Route::get('/myReceivedArticles', [MonoprutController::class, 'myReceivedArticles'])->middleware(EnsureTokenIsValid::class);
Route::post('/markAsRetrieved', [MonoprutController::class, 'markAsRetrieved'])->middleware(EnsureTokenIsValid::class);
Route::post('/cancelReservation', [MonoprutController::class, 'cancelReservation'])->middleware(EnsureTokenIsValid::class);
Route::post('/deleteArticle', [MonoprutController::class, 'deleteArticle'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************************/

/************************************************************** RGPD *************************************************************/
Route::post('/rgpd/anonymize-my-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeMyData'])->middleware(EnsureTokenIsValid::class);
Route::post('/rgpd/delete-my-data', [\App\Http\Controllers\RgpdController::class, 'deleteMyData'])->middleware(EnsureTokenIsValid::class);
Route::get('/rgpd/export-my-data', [\App\Http\Controllers\RgpdController::class, 'exportMyData'])->middleware(EnsureTokenIsValid::class);
Route::post('/rgpd/anonymize-all-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeAllData']);
Route::post('/rgpd/delete-all-data', [\App\Http\Controllers\RgpdController::class, 'deleteAllData']);
/**********************************************************************************************************************************************/

// require __DIR__.'/auth.php';
