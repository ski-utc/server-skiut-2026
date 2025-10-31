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
     * Récupère le média de preuve d'un défi (image ou vidéo)
     */
    public function getProofMedia(Request $request)
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
                'media' => asset($proof->file),
                'mediaType' => $proof->media_type ?? 'image',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la récupération de la preuve de défi : '.$e]);
        }
    }

    /**
     * Envoie une preuve d'un défi (image ou vidéo)
     */
    public function uploadProofMedia(Request $request)
    {
        $id = $request->user['id'];
        $user = User::with('room')->where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }
        $userRoomId = $user->roomID;

        $defiId = $request->input('defiId');

        if (!$request->hasFile('media')) {
            return response()->json(['success' => false, 'message' => 'Aucun média fourni'], 400);
        }

        $file = $request->file('media');
        $mediaType = $request->input('mediaType', 'image');

        // Types de fichiers supportés
        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedVideoTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo']; // mp4, mov, avi
        $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);

        if (!$file->isValid() || !in_array($file->getMimeType(), $allowedTypes)) {
            return response()->json(['success' => false, 'message' => 'Fichier invalide ou non pris en charge'], 400);
        }

        // Déterminer le type de média réel basé sur le MIME type
        $actualMediaType = in_array($file->getMimeType(), $allowedVideoTypes) ? 'video' : 'image';

        // Déterminer l'extension et le dossier
        $isVideo = ($actualMediaType === 'video');
        $extension = $isVideo ? '.mp4' : '.jpg';
        $folder = $isVideo ? 'defiProofVideos' : 'defiProofImages';

        // Vérifier les tailles de fichiers
        $maxSize = $isVideo ? 15 * 1024 * 1024 : 5 * 1024 * 1024; // 15MB pour vidéos, 5MB pour images
        if ($file->getSize() > $maxSize) {
            $maxSizeText = $isVideo ? '15MB' : '5MB';
            return response()->json(['success' => false, 'message' => "Fichier trop volumineux (max: {$maxSizeText})"], 400);
        }

        try {
            // Supprimer l'ancienne preuve si elle existe
            $existingProof = ChallengeProof::where('challenge_id', $defiId)
                ->where('room_id', $userRoomId)
                ->first();

            if ($existingProof) {
                // Supprimer l'ancien fichier
                $oldPath = str_replace('storage/', '', $existingProof->file);
                if (\Storage::disk('public')->exists($oldPath)) {
                    \Storage::disk('public')->delete($oldPath);
                }
                $existingProof->delete();
            }

            $filename = "challenge_{$defiId}_room_{$userRoomId}_" . time() . $extension;
            $filePath = $file->storeAs($folder, $filename, 'public');

            ChallengeProof::create([
                'file' => 'storage/' . $filePath,
                'media_type' => $actualMediaType,
                'challenge_id' => $defiId,
                'room_id' => $userRoomId,
                'user_id' => $id
            ]);

            return response()->json(['success' => true, 'message' => 'Défi envoyé avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du téléversement du défi : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Supprime une preuve d'un défi (image ou vidéo)
     */
    public function deleteProofMedia(Request $request)
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

            $mediaPath = $proof->file;

            if (!$mediaPath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pas de média associé à ce défi',
                ]);
            }

            $relativePath = str_replace('storage/', '', $mediaPath);

            if (!Storage::disk('public')->exists($relativePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Média introuvable dans le stockage',
                ]);
            }

            Storage::disk('public')->delete($relativePath);

            $proof->delete = true;
            $proof->delete();

            return response()->json([
                'success' => true,
                'message' => 'Défi supprimé avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }
}
