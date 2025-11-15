<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnecdoteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassementController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DefisController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MonoprutController;
use App\Http\Controllers\NavetteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermanenceController;
use App\Http\Controllers\PerformanceController;
use App\Http\Controllers\PlanningController;
use App\Http\Controllers\PushTokenController;
use App\Http\Controllers\RoomTourController;
use App\Http\Controllers\SkinderController;
use App\Http\Middleware\EnsureAdminTokenIsValid;
use App\Http\Middleware\EnsureTokenIsValid;
use Illuminate\Support\Facades\Route;

/* Auth */
Route::get('/connected', function () { return view('api-connected');})->name('api-connected');
Route::get('/notConnected', function () { return view('api-not-connected');})->name('api-not-connected');

Route::middleware([EnsureTokenIsValid::class])->group(function () {
    /* Auth */
    Route::get('/auth/me', [AuthController::class, 'getUserData']);

    /* Home */
    Route::get('/home/random-data', [HomeController::class, 'getRandomData']);
    Route::get('/home/weather', [HomeController::class, 'getWeather']);

    /* Planning */
    Route::get('/planning', [PlanningController::class, 'getPlanning']);

    /* Challenges */
    Route::get('/challenges', [DefisController::class, 'getChallenges']);
    Route::get('/challenges/proof-media/{challengeId}', [DefisController::class, 'getProofMedia']);
    Route::get('/challenges/max-file-size', [DefisController::class, 'getMaxFileSize']);
    Route::post('/challenges/proof-media', [DefisController::class, 'uploadProofMedia']);
    Route::delete('/challenges/proof-media/{mediaId}', [DefisController::class, 'deleteProofMedia']);
    Route::get('/classement-chambres', [ClassementController::class, 'classementChambres']);

    /* Anecdotes */
    Route::get('/anecdotes', [AnecdoteController::class, 'getAnecdotes']);
    Route::post('/anecdotes', [AnecdoteController::class, 'sendAnecdote']);
    Route::post('/anecdotes/{anecdoteId}/like', [AnecdoteController::class, 'likeAnecdote']);
    Route::post('/anecdotes/{anecdoteId}/warn', [AnecdoteController::class, 'warnAnecdote']);
    Route::delete('/anecdotes/{anecdoteId}', [AnecdoteController::class, 'deleteAnecdote']);

    /* Contacts */
    Route::get('/contacts', [ContactController::class, 'getContacts']);

    /* Navettes */
    Route::get('/navettes', [NavetteController::class, 'getNavettes']);

    /* Skinder */
    Route::get('/skinder/profiles', [SkinderController::class, 'getProfilSkinder']);
    Route::get('/skinder/my-profile', [SkinderController::class, 'getMyProfilSkinder']);
    Route::put('/skinder/my-profile', [SkinderController::class, 'modifyProfil']);
    Route::post('/skinder/profiles/{profileId}/like', [SkinderController::class, 'likeSkinder']);
    Route::get('/skinder/matches', [SkinderController::class, 'getMySkinderMatches']);
    Route::post('/skinder/my-profile/image', [SkinderController::class, 'uploadRoomImage']);
    Route::get('/skinder/rooms/{roomId}', [SkinderController::class, 'getRoomDetails']);

    /* Vitesse de glisse */
    Route::post('/create-performance', [PerformanceController::class, 'createPerformance']);
    Route::get('/user-performances', [PerformanceController::class, 'getUserPerformances']);
    Route::delete('/user-performances/{sessionId}', [PerformanceController::class, 'deletePerformanceSession']);
    Route::get('/classement-performances', [ClassementController::class, 'classementPerformances']);

    /* Monoprut */
    Route::get('/articles', [MonoprutController::class, 'getArticles']);
    Route::post('/articles', [MonoprutController::class, 'createArticle']);
    Route::post('/articles/{articleId}/shotgun', [MonoprutController::class, 'shotgunArticle']);
    Route::get('/articles/given', [MonoprutController::class, 'myGivenArticles']);
    Route::get('/articles/received', [MonoprutController::class, 'myReceivedArticles']);
    Route::put('/articles/{articleId}/retrieve', [MonoprutController::class, 'markAsRetrieved']);
    Route::post('/articles/{articleId}/cancel-reservation', [MonoprutController::class, 'cancelReservation']);
    Route::delete('/articles/{articleId}', [MonoprutController::class, 'deleteArticle']);

    /* Permanences */
    Route::get('/permanences/my', [PermanenceController::class, 'getUserPermanences']);

    /* Tournée des chambres */
    Route::get('/room-tours/my-tour', [RoomTourController::class, 'getUserTour']);
    Route::get('/room-tours/status', [RoomTourController::class, 'getTourStatusForTraveler']);
    Route::post('/room-tours/visits/{visitId}/mark-visited', [RoomTourController::class, 'markRoomVisited']);
    Route::post('/room-tours/visits/{visitId}/unmark-visited', [RoomTourController::class, 'unmarkVisited']);
    Route::post('/room-tours/my-tour/reorder', [RoomTourController::class, 'reorderRooms']);

    /* Push Tokens */
    Route::post('/push-tokens', [PushTokenController::class, 'store']);
    Route::get('/push-tokens', [PushTokenController::class, 'index']);
    Route::post('/push-tokens/deactivate', [PushTokenController::class, 'deactivate']);
    Route::delete('/push-tokens', [PushTokenController::class, 'destroy']);

    /* Notifications */
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'getNotifications']);
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);

    /* RGPD */
    Route::post('/rgpd/anonymize-my-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeMyData']);
    Route::delete('/rgpd/delete-my-data', [\App\Http\Controllers\RgpdController::class, 'deleteMyData']);
    Route::get('/rgpd/export-my-data', [\App\Http\Controllers\RgpdController::class, 'exportMyData']);
    Route::post('/rgpd/anonymize-all-data', [\App\Http\Controllers\RgpdController::class, 'anonymizeAllData']);
    Route::delete('/rgpd/delete-all-data', [\App\Http\Controllers\RgpdController::class, 'deleteAllData']);
});

Route::middleware([EnsureAdminTokenIsValid::class])->group(function () {
    /* Auth */
    Route::get('/admin', [AdminController::class, 'getAdmin']);

    /* Anecdotes */
    Route::get('/admin/anecdotes', [AdminController::class, 'getAdminAnecdotes']);
    Route::get('/admin/anecdotes/{anecdoteId}', [AdminController::class, 'getAnecdoteDetails']);
    Route::put('/admin/anecdotes/{anecdoteId}/status', [AdminController::class, 'updateAnecdoteStatus']);

    /* Challenges */
    Route::get('/admin/challenges', [AdminController::class, 'getAdminChallenges']);
    Route::get('/admin/challenges/{challengeId}', [AdminController::class, 'getChallengeDetails']);
    Route::put('/admin/challenges/{challengeId}/status', [AdminController::class, 'updateChallengeStatus']);

    /* Permanences */
    Route::get('/admin/permanences/members', [PermanenceController::class, 'getAssociationMembers']);
    Route::post('/admin/permanences/send-reminders', [PermanenceController::class, 'sendReminders']);
    Route::get('/admin/permanences', [PermanenceController::class, 'getAllPermanences']);
    Route::post('/admin/permanences', [PermanenceController::class, 'createPermanence']);
    Route::put('/admin/permanences/{id}', [PermanenceController::class, 'updatePermanence']);
    Route::delete('/admin/permanences/{id}', [PermanenceController::class, 'deletePermanence']);

    /* Tournée des chambres */
    Route::get('/admin/room-tours', [RoomTourController::class, 'getAllTours']);
    Route::post('/admin/room-tours', [RoomTourController::class, 'createTour']);
    Route::post('/admin/room-tours/{tourId}/toggle', [RoomTourController::class, 'toggleTour']);
    Route::delete('/admin/room-tours/{tourId}', [RoomTourController::class, 'deleteTour']);
    Route::get('/admin/room-tours/available-rooms', [RoomTourController::class, 'getAvailableRooms']);

    /* Notifications */
    Route::get('/admin/notifications/recipients', [NotificationController::class, 'getRecipientsData']);
    Route::get('/admin/notifications', [NotificationController::class, 'getAdminNotifications']);
    Route::get('/admin/notifications/{notificationId}', [NotificationController::class, 'getNotificationDetails']);
    Route::post('/admin/notifications', [NotificationController::class, 'createNotification']);
    Route::put('/admin/notifications/{id}/display', [NotificationController::class, 'toggleDisplay']);
    Route::delete('/admin/notifications/{id}', [NotificationController::class, 'deleteNotification']);
});
