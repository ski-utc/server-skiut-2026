<?php

use Illuminate\Support\Facades\Route;

/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    $ php artisan route:clear
    $ php artisan route:list
*/

Route::get('/home', function () {
    return view("welcome");
});

Route::get('https://cas.utc.fr/cas/login?service=')->name('login');

Route::get('/getTrucDuServeur', [\App\Http\Controllers\Example::class, 'exampleFunction']);