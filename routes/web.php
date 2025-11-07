<?php

use App\Http\Controllers\AuthBackOfficeController;
use App\Http\Controllers\ShotgunController;
use Illuminate\Support\Facades\Route;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

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
Route::get('/auth/back-office/login', [AuthBackOfficeController::class, 'login'])->name('backoffice.login');
Route::get('/auth/back-office/callback', [AuthBackOfficeController::class, 'callback'])->name('backoffice.callback');
Route::get('/auth/back-office/logout', [AuthBackOfficeController::class, 'logout'])->name('backoffice.logout');
/**********************************************************************************************************************************************/

require __DIR__.'/auth.php';
