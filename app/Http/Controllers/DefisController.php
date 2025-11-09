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
     * Get the challenges for the connected user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getChallenges(Request $request)
    {
        try {
            $id = $request->user['id'];
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            $userRoomId = $user->room_id;

            $challenges = Challenge::with(['challengeProofs' => function ($query) use ($userRoomId) {
                $query->where('room_id', $userRoomId);
            }])->get();

            $challengeData = $challenges->map(function ($challenge) use ($userRoomId) {
                $proof = $challenge->challengeProofs->first();

                $status = 'empty';
                if ($proof) {
                    if ($proof->valid && !$proof->delete) {
                        $status = 'done';
                    } elseif ($proof->valid && $proof->delete) {
                        $status = 'refused';
                    } else {
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
     * Get the media of a challenge proof (image or video)
     * @param Request $request
     * @param int $challengeId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getProofMedia(Request $request, $challengeId)
    {
        try {
            $id = $request->user['id'];
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }
            $userRoomId = $user->room_id;

            $proof = ChallengeProof::where('challenge_id', $challengeId)->where('room_id', $userRoomId)->first();

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
     * Get the max file size for a challenge proof
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getMaxFileSize()
    {
        return response()->json(['success' => true, 'data' => 1024 * 1024 * 5]);
    }

    /**
     * Upload a challenge proof (image or video)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadProofMedia(Request $request)
    {
        $validated = $request->validate([
            'defiId' => 'required|integer|exists:challenges,id',
            'media' => 'required|file|mimes:jpeg,png,gif,mp4,quicktime,x-msvideo|max:15360',
            'mediaType' => 'nullable|string|in:image,video',
        ]);

        $id = $request->user['id'];
        $user = User::with('room')->where('id', $id)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }
        $userRoomId = $user->room_id;

        $defiId = $validated['defiId'];
        $file = $request->file('media');
        $mediaType = $validated['mediaType'] ?? 'image';


        $allowedImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedVideoTypes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];
        $allowedTypes = array_merge($allowedImageTypes, $allowedVideoTypes);

        if (!$file->isValid() || !in_array($file->getMimeType(), $allowedTypes)) {
            return response()->json(['success' => false, 'message' => 'Fichier invalide ou non pris en charge'], 400);
        }


        $actualMediaType = in_array($file->getMimeType(), $allowedVideoTypes) ? 'video' : 'image';


        $isVideo = ($actualMediaType === 'video');
        $extension = $isVideo ? '.mp4' : '.jpg';
        $folder = $isVideo ? 'defiProofVideos' : 'defiProofImages';


        $maxSize = $isVideo ? 15 * 1024 * 1024 : 5 * 1024 * 1024; // 15MB pour vidéos, 5MB pour images
        if ($file->getSize() > $maxSize) {
            $maxSizeText = $isVideo ? '15MB' : '5MB';
            return response()->json(['success' => false, 'message' => "Fichier trop volumineux (max: {$maxSizeText})"], 400);
        }

        try {

            $existingProof = ChallengeProof::where('challenge_id', $defiId)
                ->where('room_id', $userRoomId)
                ->first();

            if ($existingProof) {

                $oldPath = str_replace('storage/', '', $existingProof->file);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
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
     * Delete a challenge proof (image or video)
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteProofMedia(Request $request)
    {
        $validated = $request->validate([
            'defiId' => 'required|integer|exists:challenges,id',
        ]);

        try {
            $id = $request->user['id'];
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            $userRoomId = $user->room_id;

            $defiId = $validated['defiId'];
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
