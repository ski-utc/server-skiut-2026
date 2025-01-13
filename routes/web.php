<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


/* 
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas : 
    php artisan route:clear
    php artisan route:list
*/



Route::get('/', function () {
    return view("welcome");
})->name('home');



require __DIR__.'/auth.php';