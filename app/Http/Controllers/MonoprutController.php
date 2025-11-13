<?php

namespace App\Http\Controllers;

use App\Models\Monoprut;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MonoprutController extends Controller
{
    /**
     * Get the articles for the connected user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getArticles(Request $request)
    {
        try {
            $articles = Monoprut::available()->get();
            return response()->json([
                'success' => true,
                'data' => $articles,
                'message' => 'Articles récupérés avec succès.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des articles: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new article
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createArticle(Request $request)
    {
        $validated = $request->validate([
            'product' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|string|max:50',
        ]);

        try {
            $id = $request->user['id'];
            $room = User::find($id)->room;
            Monoprut::create(
                [
                    'product' => $validated['product'],
                    'quantity' => $validated['quantity'],
                    'type' => $validated['type'],
                    'giver_room_id' => $room->id,
                ]
            );
            return response()->json([
                'success' => true,
                'message' => 'Article créé avec succès.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de l\'article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la création de l\'article : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Shotgun an article
     * @param Request $request
     * @param int $articleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function shotgunArticle(Request $request, $articleId)
    {
        try {
            $article = Monoprut::findOrFail($articleId);

            if (!$article->isAvailable()) {
                return response()->json([
                    'success' => false,
                    'message' => "Article déjà shotgun par quelqu'un, sorryyy",
                ], 400);
            }

            $id = $request->user['id'];
            $receiverUser = User::find($id);

            if (!$receiverUser || !$receiverUser->room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou non assigné à une chambre',
                ], 404);
            }

            $room = $receiverUser->room;
            $article->shotgunBy($room->id);

            try {
                $giverRoom = $article->giverRoom;
                if ($giverRoom) {
                    $giverUsers = $giverRoom->getUserIds();
                    if (!empty($giverUsers)) {
                        $firebaseService = app(\App\Services\FirebaseNotificationService::class);
                        $firebaseService->sendNotification(
                            $giverUsers,
                            'Monoprut shotgun !',
                            "Votre article '{$article->product}' a été shotgun par la chambre {$room->roomNumber} !",
                            [
                                'type' => 'monoprut_shotgun',
                                'article_id' => $article->id,
                                'receiver_room' => $room->roomNumber
                            ]
                        );
                    }
                }
            } catch (\Exception $e) {
                Log::error('Erreur lors de l\'envoi de la notification Monoprut: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Article shotgun avec succès.',
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du shotgun de l\'article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors du shotgun de l\'article : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the articles given by the connected user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function myGivenArticles(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::find($id);

            if (!$user || !$user->room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou non assigné à une chambre',
                ], 404);
            }

            $room = $user->room;
            $articles = Monoprut::givenBy($room->id)->get();

            $articles = $articles->map(function ($article) {
                if ($article->receiver_room_id) {
                    $receiverRoom = Room::find($article->receiver_room_id);

                    if ($receiverRoom) {
                        $receiverResponsible = User::find($receiverRoom->user_id);
                        if ($receiverResponsible) {
                            $article->receiver_info = [
                                'responsible_name' => $receiverResponsible->firstName . ' ' . $receiverResponsible->lastName,
                                'room' => $receiverRoom->roomNumber
                            ];
                        }
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
            Log::error('Erreur lors de la récupération des articles giver par l\'utilisateur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles giver par l\'utilisateur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get the articles received by the connected user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function myReceivedArticles(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::find($id);

            if (!$user || !$user->room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou non assigné à une chambre',
                ], 404);
            }

            $room = $user->room;
            $articles = Monoprut::receivedBy($room->id)
                ->notRetrieved()
                ->get();

            $articles = $articles->map(function ($article) {
                $giverRoom = Room::find($article->giver_room_id);
                if ($giverRoom) {
                    $giverResponsible = User::find($giverRoom->user_id);
                    $article->giver_info = [
                        'responsible_name' => $giverResponsible ? $giverResponsible->firstName . ' ' . $giverResponsible->lastName : 'N/A',
                        'room' => $giverRoom->roomNumber,
                        'room_name' => $giverRoom->name
                    ];

                    $article->giver_room = [
                        'id' => $giverRoom->id,
                        'roomNumber' => $giverRoom->roomNumber,
                        'name' => $giverRoom->name
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
            Log::error('Erreur lors de la récupération des articles reçus par l\'utilisateur: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles reçus par l\'utilisateur : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark an article as retrieved
     * @param Request $request
     * @param int $articleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function markAsRetrieved(Request $request, $articleId)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user || !$user->room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou non assigné à une chambre',
                ], 404);
            }

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
            Log::error('Erreur lors de la mise à jour du statut de l\'article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an article
     * @param Request $request
     * @param int $articleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteArticle(Request $request, $articleId)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé.',
                ], 404);
            }

            $article = Monoprut::find($articleId);
            if (!$article) {
                return response()->json([
                    'success' => false,
                    'message' => 'Article non trouvé.',
                ], 404);
            } elseif (!$article->isGiverRoom($user)) {
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
            Log::error('Erreur lors de la suppression de l\'article: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la suppression de l\'article : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel a reservation
     * @param Request $request
     * @param int $articleId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function cancelReservation(Request $request, $articleId)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user || !$user->room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou non assigné à une chambre',
                ], 404);
            }

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
            Log::error('Erreur lors de l\'annulation de la réservation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }
}
