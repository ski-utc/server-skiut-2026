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
            $room = User::find($id)->room;
            $article->receiver_room_id = $room;
            $article->save();

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
            $articles = Monoprut::where('receiver_room_id', $room->id)->get();
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
}