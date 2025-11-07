<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnecdoteController;
use App\Http\Controllers\ClassementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DefisController;
use App\Http\Controllers\MonoprutController;
use App\Http\Controllers\PushTokenController;
use App\Http\Controllers\SkinderController;
use App\Http\Controllers\UserPerformanceController;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Support\Facades\Route;

/************************************************************** Login *************************************************************/
Route::get('/connected', function () { return view('api-connected');})->name('api-connected');
Route::get('/notConnected', function () { return view('api-not-connected');})->name('api-not-connected');
Route::get('/auth/me', [\App\Http\Controllers\AuthController::class, 'getUserData'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Home *************************************************************/
Route::get('/home/random-data', [\App\Http\Controllers\HomeController::class, 'getRandomData'])->middleware(EnsureTokenIsValid::class);
Route::get('/home/weather', [\App\Http\Controllers\HomeController::class, 'getWeather'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************/

/************************************************************** Notifications *************************************************************/
Route::middleware(EnsureTokenIsValid::class)->group(function () {
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
});

Route::middleware([EnsureTokenIsValid::class, AdminMiddleware::class])->group(function () {
    Route::post('/notifications', [\App\Http\Controllers\NotificationController::class, 'createNotification']);
    Route::put('/notifications/{id}/display', [\App\Http\Controllers\NotificationController::class, 'toggleDisplay']);
    Route::delete('/notifications/{id}', [\App\Http\Controllers\NotificationController::class, 'deleteNotification']);
    Route::get('/notifications/recipients', [\App\Http\Controllers\NotificationController::class, 'getRecipientsData']);
});
/**************************************************************************************************************************************/

/************************************************************** Push Tokens *************************************************************/
Route::middleware(EnsureTokenIsValid::class)->group(function () {
    Route::post('/push-tokens', [PushTokenController::class, 'store']);
    Route::get('/push-tokens', [PushTokenController::class, 'index']);
    Route::post('/push-tokens/deactivate', [PushTokenController::class, 'deactivate']);
    Route::delete('/push-tokens', [PushTokenController::class, 'destroy']);
});
/**************************************************************************************************************************************/

/************************************************************** Planning *************************************************************/
Route::get('/planning', [\App\Http\Controllers\PlanningController::class, 'getPlanning'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Défis *************************************************************/
Route::get('/challenges', [DefisController::class, 'getChallenges'])->middleware(EnsureTokenIsValid::class);

Route::get('/challenges/proof-media/{challengeId}', [DefisController::class, 'getProofMedia'])->middleware(EnsureTokenIsValid::class);
Route::post('/challenges/proof-media', [DefisController::class, 'uploadProofMedia'])->middleware(EnsureTokenIsValid::class);
Route::delete('/challenges/proof-media/{mediaId}', [DefisController::class, 'deleteProofMedia'])->middleware(EnsureTokenIsValid::class);

Route::get('/classement-chambres', [ClassementController::class, 'classementChambres'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Anecdotes *************************************************************/
Route::get('/anecdotes', [AnecdoteController::class, 'getAnecdotes'])->middleware(EnsureTokenIsValid::class);
Route::post('/anecdotes', [AnecdoteController::class, 'sendAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/anecdotes/{anecdoteId}/like', [AnecdoteController::class, 'likeAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::post('/anecdotes/{anecdoteId}/warn', [AnecdoteController::class, 'warnAnecdote'])->middleware(EnsureTokenIsValid::class);
Route::delete('/anecdotes/{anecdoteId}', [AnecdoteController::class, 'deleteAnecdote'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Contacts *************************************************************/
Route::get('/contacts', [ContactController::class, 'getContacts'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Navettes *************************************************************/
Route::get('/navettes', [\App\Http\Controllers\NavetteController::class, 'getNavettes'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/*************************************************************** Skinder **************************************************************/
Route::get('/skinder/profiles', [SkinderController::class, 'getProfilSkinder'])->middleware(EnsureTokenIsValid::class);
Route::get('/skinder/my-profile', [SkinderController::class, 'getMyProfilSkinder'])->middleware(EnsureTokenIsValid::class);
Route::put('/skinder/my-profile', [SkinderController::class, 'modifyProfil'])->middleware(EnsureTokenIsValid::class);
Route::post('/skinder/profiles/{profileId}/like', [SkinderController::class, 'likeSkinder'])->middleware(EnsureTokenIsValid::class);
Route::get('/skinder/matches', [SkinderController::class, 'getMySkinderMatches'])->middleware(EnsureTokenIsValid::class);
Route::post('/skinder/my-profile/image', [SkinderController::class, 'uploadRoomImage'])->middleware(EnsureTokenIsValid::class);
Route::get('/skinder/rooms/{roomId}', [SkinderController::class, 'getRoomDetails'])->middleware(EnsureTokenIsValid::class);
/**************************************************************************************************************************************/

/************************************************************** Administration *************************************************************/
Route::get('/admin', [AdminController::class, 'getAdmin'])->middleware([EnsureTokenIsValid::class]);

Route::get('/admin/challenges', [AdminController::class, 'getAdminChallenges'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/admin/challenges/{challengeId}', [AdminController::class, 'getChallengeDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::put('/admin/challenges/{challengeId}/status', [AdminController::class, 'updateChallengeStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/admin/anecdotes', [AdminController::class, 'getAdminAnecdotes'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/admin/anecdotes/{anecdoteId}', [AdminController::class, 'getAnecdoteDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::put('/admin/anecdotes/{anecdoteId}/status', [AdminController::class, 'updateAnecdoteStatus'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/admin/notifications', [\App\Http\Controllers\NotificationController::class, 'getAdminNotifications'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::get('/admin/notifications/{notificationId}', [AdminController::class, 'getNotificationDetails'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::put('/admin/notifications/{notificationId}/display', [AdminController::class, 'displayNotification'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/admin/notifications/send-all', [AdminController::class, 'sendNotificationToAll'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);
Route::post('/admin/notifications/{userId}/send-individual', [AdminController::class, 'sendIndividualNotification'])->middleware([EnsureTokenIsValid::class, AdminMiddleware::class]);

Route::get('/users/max-file-size', [\App\Http\Controllers\UserController::class, 'getMaxFileSize'])->middleware([EnsureTokenIsValid::class]);
Route::post('/users/push-token', [\App\Http\Controllers\UserController::class, 'saveToken'])->middleware([EnsureTokenIsValid::class]);
/*********************************************************************************************************************************************/

/************************************************************** Permanences *************************************************************/
Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/permanences/my', [\App\Http\Controllers\PermanenceController::class, 'getUserPermanences']);
});

Route::middleware([EnsureTokenIsValid::class, AdminMiddleware::class])->group(function () {
    Route::get('/permanences', [\App\Http\Controllers\PermanenceController::class, 'getAllPermanences']);
    Route::post('/permanences', [\App\Http\Controllers\PermanenceController::class, 'createPermanence']);
    Route::put('/permanences/{id}', [\App\Http\Controllers\PermanenceController::class, 'updatePermanence']);
    Route::delete('/permanences/{id}', [\App\Http\Controllers\PermanenceController::class, 'deletePermanence']);
    Route::get('/permanences/members', [\App\Http\Controllers\PermanenceController::class, 'getAssociationMembers']);
    Route::post('/permanences/send-reminders', [\App\Http\Controllers\PermanenceController::class, 'sendReminders']);
});
/*********************************************************************************************************************************************/

/************************************************************** Tournée des chambres *************************************************************/
Route::middleware([EnsureTokenIsValid::class])->group(function () {
    Route::get('/room-tours/my', [\App\Http\Controllers\RoomTourController::class, 'getUserTour']);
    Route::get('/room-tours/status', [\App\Http\Controllers\RoomTourController::class, 'getTourStatusForTraveler']);
    Route::post('/room-tours/visits/{visitId}/mark-visited', [\App\Http\Controllers\RoomTourController::class, 'markRoomVisited']);
    Route::post('/room-tours/visits/{visitId}/unmark-visited', [\App\Http\Controllers\RoomTourController::class, 'unmarkVisited']);
    Route::post('/room-tours/my/reorder', [\App\Http\Controllers\RoomTourController::class, 'reorderRooms']);
});

Route::middleware([EnsureTokenIsValid::class, AdminMiddleware::class])->group(function () {
    Route::get('/room-tours', [\App\Http\Controllers\RoomTourController::class, 'getAllTours']);
    Route::post('/room-tours', [\App\Http\Controllers\RoomTourController::class, 'createTour']);
    Route::post('/room-tours/{tourId}/toggle', [\App\Http\Controllers\RoomTourController::class, 'toggleTour']);
    Route::delete('/room-tours/{tourId}', [\App\Http\Controllers\RoomTourController::class, 'deleteTour']);
    Route::get('/room-tours/available-rooms', [\App\Http\Controllers\RoomTourController::class, 'getAvailableRooms']);
});
/*********************************************************************************************************************************************/

/************************************************************** Vitesse de glisse *************************************************************/
Route::put('/user-performances', [UserPerformanceController::class, 'updatePerformance'])->middleware(EnsureTokenIsValid::class);
Route::get('/user-performances', [UserPerformanceController::class, 'getUserPerformances'])->middleware(EnsureTokenIsValid::class);
Route::delete('/user-performances/{sessionId}', [UserPerformanceController::class, 'deletePerformanceSession'])->middleware(EnsureTokenIsValid::class);
Route::get('/classement-performances', [ClassementController::class, 'classementPerformances'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************************/

/************************************************************** Monoprut *************************************************************/
Route::get('/articles', [MonoprutController::class, 'getArticles'])->middleware(EnsureTokenIsValid::class);
Route::post('/articles', [MonoprutController::class, 'createArticle'])->middleware(EnsureTokenIsValid::class);
Route::post('/articles/{articleId}/shotgun', [MonoprutController::class, 'shotgunArticle'])->middleware(EnsureTokenIsValid::class);
Route::get('/articles/given', [MonoprutController::class, 'myGivenArticles'])->middleware(EnsureTokenIsValid::class);
Route::get('/articles/received', [MonoprutController::class, 'myReceivedArticles'])->middleware(EnsureTokenIsValid::class);
Route::put('/articles/{articleId}/retrieve', [MonoprutController::class, 'markAsRetrieved'])->middleware(EnsureTokenIsValid::class);
Route::post('/articles/{articleId}/cancel-reservation', [MonoprutController::class, 'cancelReservation'])->middleware(EnsureTokenIsValid::class);
Route::delete('/articles/{articleId}', [MonoprutController::class, 'deleteArticle'])->middleware(EnsureTokenIsValid::class);
/**********************************************************************************************************************************************/

/************************************************************** RGPD *************************************************************/
Route::post('/rgpd/anonymize-my-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeMyData'])->middleware(EnsureTokenIsValid::class);
Route::delete('/rgpd/my-data', [\App\Http\Controllers\RgpdController::class, 'deleteMyData'])->middleware(EnsureTokenIsValid::class);
Route::get('/rgpd/export-my-data', [\App\Http\Controllers\RgpdController::class, 'exportMyData'])->middleware(EnsureTokenIsValid::class);
Route::post('/rgpd/anonymize-all-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeAllData']);
Route::delete('/rgpd/all-data', [\App\Http\Controllers\RgpdController::class, 'deleteAllData']);
/**********************************************************************************************************************************************/

// require __DIR__.'/auth.php';
