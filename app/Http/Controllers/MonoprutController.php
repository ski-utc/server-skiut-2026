<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Monoprut;

class MonoprutController extends Controller
{
    public function getArticles(Request $request) {
        try {
            $articles = Monoprut::where('receiver_room_id', null)->get();
            return response()->json([
                'success' => true,
                'data' => $articles,
                'message' => 'Articles récupérés avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createArticle(Request $request) {
        try {
            $id = $request->user['id'];
            $room = User::find($id)->room;
            Monoprut::create(
                [
                    'product' => $request->input('product'),
                    'quantity' => $request->input('quantity'),
                    'type' => $request->input('type'),
                    'giver_room_id' => $room->id,
                ]
            );
            return response()->json([
                'success' => true,
                'message' => 'Article créé avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'article : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function shotgunArticle(Request $request) {
        try {
            $articleId = $request->input('articleId');
            $article = Monoprut::where('id', $articleId)->first();
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé.',
                ], 404);
            } else if ($article->receiver_room_id != null) {
                return response()->json([
                    'success' => false,
                    'message' => "Article déjà shotgun par quelqu'un, sorryyy",
                ], 400);
            }

            $id = $request->user['id'];
            $receiverUser = User::find($id);
            $room = $receiverUser->room;
            $article->receiver_room_id = $room->id;
            $article->save();

            // TODO: Envoyer une notification au donneur (FirebaseService à implémenter)
            // $giverRoom = $article->giver;
            // if ($giverRoom) {
            //     $giverUsers = User::where('roomID', $giverRoom->roomNumber)->pluck('id')->toArray();
            //     if (!empty($giverUsers)) {
            //         // Envoyer notification
            //     }
            // }

            return response()->json([
                'success' => true,
                'message' => 'Article shotgun avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du shotgun de l\'article : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function myGivenArticles(Request $request) {
        try {
            $id = $request->user['id'];
            $room = User::find($id)->room;
            $articles = Monoprut::where('giver_room_id', $room->id)->get();
            
            $articles = $articles->map(function ($article) {
                if ($article->receiver_room_id) {
                    $receiverRoom = $article->receiver;
                    // Récupérer le responsable de la chambre
                    $receiverResponsible = $receiverRoom->respUser;
                    if ($receiverResponsible) {
                        $article->receiver_info = [
                            'responsible_name' => $receiverResponsible->firstName . ' ' . $receiverResponsible->lastName,
                            'room' => $receiverRoom->roomNumber
                        ];
                    }
                }
                return $article;
            });
            
            return response()->json([
                'success' => true,
                'data' => $articles,
                'message' => 'Articles récupérés avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles giver par l\'utilisateur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function myReceivedArticles(Request $request) {
        try {
            $id = $request->user['id'];
            $room = User::find($id)->room;
            $articles = Monoprut::where('receiver_room_id', $room->id)
                ->where('retrieved', false)
                ->get();
            
            $articles = $articles->map(function ($article) {
                $giverRoom = $article->giver;
                $giverResponsible = $giverRoom->respUser;
                if ($giverResponsible) {
                    $article->giver_info = [
                        'responsible_name' => $giverResponsible->firstName . ' ' . $giverResponsible->lastName,
                        'room' => $giverRoom->roomNumber
                    ];
                }
                return $article;
            });
            
            return response()->json([
                'success' => true,
                'data' => $articles,
                'message' => 'Articles récupérés avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles reçus par l\'utilisateur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function markAsRetrieved(Request $request) {
        try {
            $articleId = $request->input('articleId');
            $userId = $request->user['id'];
            $user = User::find($userId);
            $room = $user->room;

            $article = Monoprut::find($articleId);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé.',
                ], 404);
            }

            if ($article->receiver_room_id != $room->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à marquer cet article comme récupéré.',
                ], 403);
            }

            $article->retrieved = true;
            $article->save();

            return response()->json([
                'success' => true,
                'message' => 'Article marqué comme récupéré.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function deleteArticle(Request $request) {
        try {
            $id = $request->input('articleId');
            $userId = $request->user['id'];
            $user = User::find($userId);
            $article = Monoprut::where('id', $id)->first();
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé.',
                ], 404);
            } else if (!$article->isGiverRoom($user)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas le giver de cet article.',
                ], 403);
            }
            $article->delete();
            return response()->json([
                'success' => true,
                'message' => 'Article supprimé avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression de l\'article : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function cancelReservation(Request $request) {
        try {
            $articleId = $request->input('articleId');
            $userId = $request->user['id'];
            $user = User::find($userId);
            $room = $user->room;

            $article = Monoprut::find($articleId);
            
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé.',
                ], 404);
            }

            if ($article->receiver_room_id != $room->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas autorisé à annuler cette réservation.',
                ], 403);
            }

            $article->receiver_room_id = null;
            $article->save();

            return response()->json([
                'success' => true,
                'message' => 'Réservation annulée avec succès.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }
}