<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Monoprut;

class MonoprutController extends Controller
{
    public function getArticles(Request $request) {
        try {
            $articles = Monoprut::where('receiver_id', null)->get();
            return response()->json([
                'success' => true,
                'data' => $articles,
                'message' => 'Articles fetched successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de la récupération des articles : ' . $e->getMessage(),
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
            } else if ($article->receiver_id != null) {
                return response()->json([
                    'success' => false,
                    'message' => "Article déjà shotgun par quelqu'un, sorryyy",
                ], 400);
            }

            $id = $request->user['id'];
            $article->receiver_id = $id;
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
}