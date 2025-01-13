<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Artisan;

class InstallController extends Controller
{
    public function installDependencies()
    {
        // Appeler la commande artisan
        Artisan::call('install:dependencies');

        // Retourner une réponse pour informer que l'installation est terminée
        return response()->json(['message' => 'Dependencies are being installed.']);
    }
}
