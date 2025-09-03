<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;


/*
Lors de la création d'une nouvelle route, si cette dernière ne fonctionne pas :
    php artisan route:clear
    php artisan route:list
*/


Route::get('/', function () {
    return file_get_contents(public_path('test.html'));
})->name('jeu1');

Route::get('/cetaitlideedelouis', function () {
    return file_get_contents(public_path('templeRun.html'));
})->name('jeu2');

/*
Route::get('/run-migrations', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);
        return response()->json(['success' => 'Les migrations ont été exécutées avec succès.']);
    } catch (\Exception $e) {
        return response()->json(['error' => 'Erreur lors de l\'exécution des migrations : ' . $e->getMessage()]);
    }
});
*/

/*
Route::get('/create-storage-link', function () {
    try {
        Artisan::call('storage:link');
        return 'Le lien symbolique a été créé avec succès !';
    } catch (\Exception $e) {
        return 'Erreur : ' . $e->getMessage();
    }
});
*/

require __DIR__.'/auth.php';
