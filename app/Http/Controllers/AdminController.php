<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChallengeProof;
use App\Models\Anecdote;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Services\ExpoPushService;

class AdminController extends Controller
{
    /**
     * Permet de vérifier si le user est admin
     */
    public function getAdmin(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::find($userId);

            if ($user && $user->admin) {
                return response()->json(['success' => true, 'message' => 'Vous êtes admin.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Vous n\'êtes pas admin.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion des défis (récupération, validation, suppression)
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

            // Récupère le défi avec les informations de l'utilisateur (prénom et nom)
            $challenge = ChallengeProof::with(['user', 'room', 'challenge'])->findOrFail($challengeId);

            return response()->json([
                'success' => true,
                'data' => $challenge,
                'imagePath'=> asset($challenge->file)
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
    public function updateChallengeStatus($challengeId, $isValid, $isDelete)
    {
        try {
            $challenge = ChallengeProof::findOrFail($challengeId);

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
     * Récupère les détails d'une anecdote spécifique par son ID
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
        try {
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
     * Récupère les notifications
     */
    public function getAdminNotifications()
    {
        try {
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
     * Récupère les détails d'une notification spécifique par son ID
     */
    public function getNotificationDetails($notificationId)
    {
        try {
            $notification = Notification::findOrFail($notificationId);

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

    /**
     * Envoie une notification à un utilisateur spécifique
     */
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

    /**
     * Envoie une notification à tous les utilisateurs
     */
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
     * Envoie une notification individuelle à un utilisateur spécifique
     */
    public function sendIndividualNotification(Request $request, $userId)
    {
        $notification = new Notification([
            'title' => $request->title,
            'text' => $request->text,
            'user_id' => $userId,
        ]);
        $notification->save();

        return response()->json(['success' => true, 'message' => 'Notification sent to user.']);
    }

    /**
     * Supprime une notification
     */
    public function deleteNotification(Request $request, $notificationId, $delete)
    {
        try {
            $notification = Notification::findOrFail($notificationId);

            if ($delete === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le paramètre "delete" est requis (1 pour supprimer, 0 pour annuler).',
                ]);
            }

            // Mise à jour du statut de suppression
            $notification->delete = $delete;
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
}
