<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\ChallengeProof;
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


    /**
     * Gestion des défis 
     */

     public function getAllChallenges(Request $request)
     {
         try {
             $publicKey = config('services.crypt.public');
             $token = $request->bearerToken();
             $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));     
             $userId = $decoded->key;
      
             $user = User::find($userId);
      
             if (!$user || !$user->admin) {
                 Log::notice('AdminController: L\'utilisateur n\'est pas un admin');
                 return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas admin.']);
             }
     
             $quantity = $request->input('quantity', 10);
      
             if (!is_numeric($quantity) || (int)$quantity <= 0) {
                 return response()->json(['success' => false, 'message' => 'Le paramètre quantity doit être un entier positif.']);
             }
      
             $challenges = Challenge::with(['room', 'user'])
                 ->withCount('likes')
                 ->orderBy('id', 'desc')
                 ->take((int)$quantity)
                 ->get();
      
             $data = $challenges->map(function ($challenge) {
                 return [
                     'id' => $challenge->id,
                     'text' => $challenge->file,
                     'nbLikes' => $challenge->nbLikes,
                     'valid' => $challenge->valid,
                     'alert' => $challenge->alert,
                     'delete' => $challenge->delete,
                     'active' => $challenge->active,
                     'authorId' => $challenge->user_id, // Récupère l'ID de l'auteur
                     'roomId' => $challenge->room_id, // Récupère l'ID de la room
                     'challengeId' => $challenge->challenge_id, // Récupère le nom de la room
                 ];
             });
      
             return response()->json(['success' => true, 'data' => $data]);
         } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
         }
     }
     


    /**
     * Gestion des anecdotes 
     */


     /**
     * Gestion des notifications 
     */
}