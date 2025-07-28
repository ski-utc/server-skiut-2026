<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeProof;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DefisController extends Controller
{
    /**
     * Récupère les défis (et le status selon le user)
     */
    public function getChallenges(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            $userRoomId = $user->roomID;

            $challenges = Challenge::with(['challengeProofs' => function ($query) use ($userRoomId) {
                $query->where('room_id', $userRoomId);
            }])->get();

            $challengeData = $challenges->map(function ($challenge) use ($userRoomId) {
                $proof = $challenge->challengeProofs->first();

                $status = 'empty';
                if ($proof) {
                    if ($proof->valid && !$proof->delete) { // validé par admin
                        $status = 'done';
                    } elseif ($proof->valid && $proof->delete) { // refusé par admin
                        $status = 'refused';
                    } else { // en attente de validation
                        $status = 'pending';
                    }

                }

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'nbPoints' => $challenge->nbPoints,
                    'status' => $status,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $challengeData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la récupération des défis : '.$e]);
        }
    }

    /**
     * Récupère l'image de preuve d'un défi
     */
    public function getProofImage(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }
            $userRoomId = $user->roomID;

            $defiId = $request->input('defiId');
            $proof = ChallengeProof::where('challenge_id', $defiId)->where('room_id', $userRoomId)->first();

            if (!$proof) {
                return response()->json([
                    'success' => false,
                    'message' => 'Défi pas encore réalisé',
                ]);
            }

            return response()->json([
                'success' => true,
                'image' => asset($proof->file),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la récupération de la preuve de défi : '.$e]);
        }
    }

    /**
     * Envoie une preuve d'un défi
     */
    public function uploadProofImage(Request $request)
    {
        $id = $request->user['id'];
        $user = User::with('room')->where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }
        $userRoomId = $user->roomID;

        $defiId = $request->input('defiId');

        if (!$request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'Aucune image fournie'], 400);
        }

        $file = $request->file('image');

        if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            return response()->json(['success' => false, 'message' => 'Fichier invalide ou non pris en charge'], 400);
        }

        try {
            $filePath = $file->storeAs('defiProofImages', "challenge_{$defiId}_room_{$userRoomId}.jpg", 'public');
            ChallengeProof::create(
                [
                    'file' => 'storage/' . $filePath,
                    'challenge_id' => $defiId,
                    'room_id' => $userRoomId,
                    'user_id' => $id
                ]
            );

            return response()->json(['success' => true, 'message' => 'Défi envoyé avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du téléversement du défi : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Supprime une preuve d'un défi
     */
    public function deleteProofImage(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            $userRoomId = $user->roomID;

            $defiId = $request->input('defiId');
            $proof = ChallengeProof::where('challenge_id', $defiId)
                ->where('room_id', $userRoomId)
                ->first();

            if (!$proof) {
                return response()->json([
                    'success' => false,
                    'message' => 'Défi pas encore réalisé',
                ]);
            }

            $photoPath = $proof->file;

            if (!$photoPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pas de photo associée à ce défi',
                ]);
            }

            $relativePath = str_replace('storage/', '', $photoPath);

            if (!Storage::disk('public')->exists($relativePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Photo introuvable dans le stockage',
                ]);
            }

            Storage::disk('public')->delete($relativePath);

            $proof->delete = true;
            $proof->delete();

            return response()->json([
                'success' => true,
                'message' => 'Défi supprimée avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }
}
