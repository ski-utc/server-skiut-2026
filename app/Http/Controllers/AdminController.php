<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\ChallengeProof;
use App\Models\Anecdote; 
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

     public function getAdminChallenges(Request $request)
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
                     'file' => $challenge->file,
                     'nbLikes' => $challenge->nb_likes,
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

     public function getAdminAnecdotes(Request $request)
    {
        try {
            // Récupération des paramètres de filtre (facultatifs)
            $filter = $request->query('filter', 'all');

            // Construire la requête de base
            $query = Anecdote::with(['user', 'likes', 'warns']);

            // Appliquer les filtres
            switch ($filter) {
                case 'pending':
                    $query->where('valid', false);
                    break;

                case 'reported':
                    $query->where('alert', '>', 0);
                    break;

                case 'all':
                default:
                    // Pas de filtre spécifique
                    break;
            }

            // Récupérer les anecdotes
            $anecdotes = $query->where('delete', false) // Exclure les anecdotes supprimées
                ->orderBy('id', 'desc') // Trier par date de création
                ->get();

            return response()->json([
                'success' => true,
                'data' => $anecdotes,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des anecdotes : ' . $e->getMessage(),
            ], 500);
        }
    }

 
     /**
      * Récupère les détails d'une anecdote spécifique par son ID
      */
      public function getAnecdoteDetails(Request $request, $id)
      {
          try {
              Log::notice('getAnecdoteDetails/' . $id);
      
              // Récupère l'anecdote avec les informations de l'utilisateur (prénom et nom)
              $anecdote = Anecdote::with(['user', 'likes', 'warns'])->findOrFail($id);
 
              Log::info($anecdote->toArray());

      
              return response()->json([
                  'success' => true,
                  'data' => $anecdote
              ]);
          } catch (\Exception $e) {
              return response()->json([
                  'success' => false,
                  'message' => 'Erreur lors de la récupération de l\'anecdote : ' . $e->getMessage(),
              ], 500);
          }
      }
      
 
     /**
      * Met à jour le statut de validation d'une anecdote (valider ou invalider)
      */
     public function updateAnecdoteStatus(Request $request, $id, $status)
     {
         try {
             $anecdote = Anecdote::findOrFail($id);
 
             $valid = $request->input('valid', null); // 1 pour valider, 0 pour invalider
 
             if ($valid === null) {
                 return response()->json([
                     'success' => false,
                     'message' => 'Le paramètre "valid" est requis (1 pour valider, 0 pour invalider).',
                 ]);
             }
 
             // Mise à jour du statut de validation
             $anecdote->valid = $valid;
             $anecdote->save();
 
             return response()->json([
                 'success' => true,
                 'message' => $valid ? 'Anecdote validée avec succès.' : 'Anecdote invalidée avec succès.',
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Erreur lors de la mise à jour du statut de l\'anecdote : ' . $e->getMessage(),
             ], 500);
         }
     }
     /**
      * Gestion des notifications
      */
 }