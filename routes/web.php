<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas :
    php artisan route:clear
    php artisan route:list
*/

Route::get('/', function () { return view('welcome');})->name('home');

Route::get('/game', [\App\Http\Controllers\ShotgunController::class, 'showGame'])->name('game');
Route::post('/submit', [\App\Http\Controllers\ShotgunController::class, 'submit'])->name('submit');

require __DIR__.'/auth.php';
