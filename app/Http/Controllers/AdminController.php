<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
 

class AdminController extends Controller
{
    // Permet de vérifier si c'est bien un admin
    public function getAdmin(Request $request)
    {
        try {
            // en-tête chiffrée à garder pour récupérer l'user 
            $publicKey = config('services.crypt.public');
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));     
            $userId = $decoded->key;

            // Récupère l'utilisateur correspondant à l'ID
            $user = User::find($userId);

            // Vérifie si l'utilisateur existe et s'il est un administrateur
            if ($user && $user->admin) {
                log::notice('AdminController: L\'utilisateur est un admin');
                return response()->json(['success' => true, 'message' => 'Vous êtes admin.']);
            } else {
                // L'utilisateur n'est pas un admin
                log::notice('AdminController: L\'utilisateur n\'est pas un admin');       
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas admin.']);
            }
        } catch (\Exception $e) {
            // Capture d'erreur
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }  
}