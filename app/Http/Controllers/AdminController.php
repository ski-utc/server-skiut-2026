<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\User;
use App\Models\ChallengeProof;
use App\Models\Challenge;
use App\Models\Anecdote;
use App\Models\Notification;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;
use App\Services\ExpoPushService;

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
        // Retrieve filter parameter (optional)
        $filter = $request->query('filter', 'all');

        // Build the base query
        $query = ChallengeProof::with(['room', 'user', 'challenge'])->where('delete', false); // Assuming these are the related models

        // Apply filters
        switch ($filter) {
            case 'pending':
                $query->where('valid', false);
                break;

            case 'valid':
                $query->where('valid', true);
                break;

            case 'all':
            default:
                break;
        }

        // Fetch challenges
        $challenges = $query->orderBy('id', 'desc') // Sort by creation date
            ->get();

        return response()->json([
            'success' => true,
            'data' => $challenges,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des défis : ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Récupère les détails d'un défi spécifique par son ID
 */
public function getChallengeDetails(Request $request, $challengeId)
{
    try {
        Log::notice('getChallengeDetails/' . $challengeId);

        // Récupère le défi avec les informations de l'utilisateur (prénom et nom)
        $challenge = ChallengeProof::with(['user', 'room', 'challenge'])->findOrFail($challengeId);

        return response()->json([
            'success' => true,
            'data' => $challenge
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération du défi : ' . $e->getMessage(),
        ], 500);
    }
}

/**
 * Met à jour le statut de validation d'un challenge (valider ou invalider)
 */
public function updateChallengeStatus(Request $request, $challengeId, $isValid, $isDelete)
{
    try {
        $publicKey = config('services.crypt.public');
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
        $userId = $decoded->key;

        $challenge = ChallengeProof::findOrFail($challengeId);
        Log::notice('challenge: ' . $challenge);

        if ($isValid === null || $isDelete === null) {
            return response()->json([
                'success' => false,
                'message' => 'Les paramètres "isValid" et "isDelete" sont requis.',
            ]);
        }

        // Mise à jour du statut de validation
        $challenge->valid = $isValid;
        $challenge->delete = $isDelete;
        $challenge->save();


        // Prépare le message 
        if ($isValid && $isDelete) {
            $message = 'Challenge refusé avec succès';
        } elseif ($isValid && !$isDelete) {
            $message = 'Challenge validé avec succès';
        } elseif (!$isValid && !$isDelete) {
            $message = 'Challenge invalidé avec succès';
        }


        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du statut du challenge : ' . $e->getMessage(),
        ], 500);
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
                     // Filtrer les anecdotes ayant plus d'un avertissement
                     $query->whereHas('warns', function($q) {
                         $q->groupBy('anecdote_id')  // Groupement par ID d'anecdote
                           ->havingRaw('COUNT(*) > 0');  // Plus d'un avertissement
                     });
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
     
             // Compter le nombre d'avertissements pour chaque anecdote
             foreach ($anecdotes as $anecdote) {
                 $anecdote->nbWarns = $anecdote->warns()->count();
             }
     
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
              $nbLikes = $anecdote->likes()->count();
              $nbWarns = $anecdote->warns()->count();


              return response()->json([
                  'success' => true,
                  'data' => $anecdote,
                  'nbLikes' => $nbLikes,
                  'nbWarns' => $nbWarns
              ]);
              Log::notice('response: ' . $response);
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
      public function updateAnecdoteStatus(Request $request, $anecdoteId, $isValid)
      {
        Log::notice('updateAnecdoteStatus/' . $anecdoteId);
        Log::notice('isValid: ' . $isValid);
          try {
            $publicKey = config('services.crypt.public');
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
            $userId = $decoded->key;

              $anecdote = Anecdote::findOrFail($anecdoteId);

              if ($isValid === null) {
                  return response()->json([
                      'success' => false,
                      'message' => 'Le paramètre "isValid" est requis (1 pour valider, 0 pour invalider).',
                  ]);
              }

              // Mise à jour du statut de validation
              $anecdote->valid = $isValid;
              $anecdote->save();

              return response()->json([
                  'success' => true,
                  'message' => $isValid ? 'Anecdote validée avec succès.' : 'Anecdote désactivée avec succès.',
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

    public function getAdminNotifications(Request $request)
    {
        try {
            // Fetch notifications sorted by creation date
            $notifications = Notification::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $notifications,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving notifications: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Récupère les détails d'un défi spécifique par son ID
     */
    public function getNotificationDetails(Request $request, $notificationId)
    {
        try {
            Log::notice('getNotificationDetails/' . $notificationId);

            // Récupère le défi avec les informations de l'utilisateur (prénom et nom)
            $notification = Notification::findOrFail($notificationId);
            Log::notice('Notification : ' . $notification);

            return response()->json([
                'success' => true,
                'data' => $notification
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du défi : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function sendNotificationToOne(Request $request)
    {
        try {
            $title = $request->input('titre');
            $body = $request->input('texte');
            $token = $request->input('token');

            $expoPushService = new ExpoPushService();
            $expoPushService->sendNotification(
                $token,
                $title,
                $body,
                $request->input('data', [])
            );

            Notification::create([
                'title' => $title,
                'description' => $body,
                'general' => false,
                'delete' => false,
            ]);

            return response()->json(['success' => true, 'message' => "Notification envoyée avec succès à l'utilisateurice !"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }

    public function sendNotificationToAll(Request $request)
    {
        try {
            $title = $request->input('titre');
            $body = $request->input('texte');
            $data = (object) [];

            $tokens = \App\Models\PushToken::pluck('token')->toArray();

            $expoPushService = new ExpoPushService();
            foreach ($tokens as $token) {
                $expoPushService->sendNotification(
                    $token,
                    $title,
                    $body,
                    $data
                );
            }

            Notification::create([
                'title' => $title,
                'description' => $body,
                'general' => true,
                'delete' => false,
            ]);

            return response()->json(['success' => true, 'message' => 'Notification envoyée à tous les utilisateurs !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur : ' . $e->getMessage()]);
        }
    }













    /**
     * Envoie une notification générale à tous les utilisateurs
     */
    public function sendGeneralNotification(Request $request)
    {
        // Logic to send a general notification to all users
        $notification = new Notification([
            'title' => $request->title,
            'text' => $request->text,
            'is_general' => true, // Flag to indicate it's a general notification
        ]);
        $notification->save();

        // You can implement broadcasting logic here (like using Firebase Cloud Messaging or Pusher)
        return response()->json(['success' => true, 'message' => 'Notification sent to all users.']);
    }

    /**
     * Envoie une notification individuelle à un utilisateur spécifique
     */
    public function sendIndividualNotification(Request $request, $userId)
    {
        // Logic to send a notification to a specific user
        $notification = new Notification([
            'title' => $request->title,
            'text' => $request->text,
            'user_id' => $userId, // Link notification to a specific user
        ]);
        $notification->save();

        return response()->json(['success' => true, 'message' => 'Notification sent to user.']);
    }

    /**
     * Delete notification
     */
    public function deleteNotification(Request $request, $notificationId, $delete)
    {
        Log::notice('delete: ' . $delete);
        try {
            $publicKey = config('services.crypt.public');
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));
            $userId = $decoded->key;

            $notification = Notification::findOrFail($notificationId); // Assuming you have a Notification model
            Log::notice('notification: ' . $notification);

            if ($delete === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le paramètre "delete" est requis (1 pour supprimer, 0 pour annuler).',
                ]);
            }

            // Mise à jour du statut de suppression
            $notification->delete = $delete; // Assuming there is a 'deleted' field in the Notification model
            $notification->save();

            return response()->json([
                'success' => true,
                'message' => $delete ? 'Notification supprimée avec succès.' : 'Suppression annulée avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du statut de la notification : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getMaxFileSize()
        {
            return response()->json(['success' => true, 'data' => 1024*1024*0.1]);
        }


 }
