<?php

use Illuminate\Support\Facades\Route;

/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    php artisan route:clear
    php artisan route:list
*/

Route::get('/', function () {
    return view("welcome");
})->middleware('auth');

Route::get('/getTrucDuServeur', [\App\Http\Controllers\ExampleController::class, 'exampleFunction']);

require __DIR__.'/auth.php';