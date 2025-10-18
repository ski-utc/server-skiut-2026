<?php

use Illuminate\Support\Facades\Route;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use App\Http\Controllers\ShotgunController;
use App\Http\Controllers\AuthBackOfficeController;

/************************************************************** Metrics *************************************************************/
Route::get('/metrics', function (CollectorRegistry $registry) {
    $renderer = new RenderTextFormat();
    return response($renderer->render($registry->getMetricFamilySamples()))
        ->header('Content-Type', RenderTextFormat::MIME_TYPE);
});
/**********************************************************************************************************************************************/

/************************************************************** Website *************************************************************/
Route::get('/', function () {return file_get_contents(public_path('next/index.html'));});
/**********************************************************************************************************************************************/

/************************************************************** RGPD *************************************************************/
Route::get('/rgpd', function () { return view('rgpd');})->name('rgpd');
/**********************************************************************************************************************************************/

/************************************************************** Shotgun Billetterie *************************************************************/
Route::get('/shotgun', [ShotgunController::class, 'showGame'])->name('game');
Route::post('/submit', [ShotgunController::class, 'submit'])->name('submit');
/***********************************************************************************************************************************************/

/************************************************************** Back-Office Shotgun Chambre ***************************************************/
Route::get('/auth/shotgun-chambre/login', [AuthBackOfficeController::class, 'login'])->name('backoffice.login');
Route::get('/auth/shotgun-chambre/callback', [AuthBackOfficeController::class, 'callback'])->name('backoffice.callback');
Route::get('/auth/shotgun-chambre/logout', [AuthBackOfficeController::class, 'logout'])->name('backoffice.logout');
/**********************************************************************************************************************************************/

require __DIR__.'/auth.php';
