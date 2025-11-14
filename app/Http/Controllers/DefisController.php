<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeProof;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DefisController extends Controller
{
    /**
     * Maximum file size for images in bytes (5MB)
     */
    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024;

    /**
     * Maximum file size for videos in bytes (15MB)
     */
    private const MAX_VIDEO_SIZE = 15 * 1024 * 1024;

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
            $user = User::with('room')->findOrFail($id);

            $userRoomId = $user->getRoomId();

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
            Log::error('Erreur lors de la récupération des défis: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la récupération des défis : '.$e], 500);
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
            $user = User::with('room')->findOrFail($id);

            $userRoomId = $user->getRoomId();

            $proof = ChallengeProof::byChallenge($challengeId)->byRoom($userRoomId)->first();

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
            Log::error('Erreur lors de la récupération de la preuve de défi: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Une erreur est survenue lors de la récupération de la preuve de défi : '.$e], 500);
        }
    }

    /**
     * Get the max file size for a challenge proof
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getMaxFileSize()
    {
        return response()->json([
            'success' => true, 
            'data' => [
                'maxImageSize' => self::MAX_IMAGE_SIZE,
                'maxVideoSize' => self::MAX_VIDEO_SIZE
            ]
        ]);
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
        $user = User::with('room')->findOrFail($id);

        $userRoomId = $user->getRoomId();

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

        $maxSize = $isVideo ? self::MAX_VIDEO_SIZE : self::MAX_IMAGE_SIZE;
        if ($file->getSize() > $maxSize) {
            $maxSizeText = $isVideo ? '15MB' : '5MB';
            return response()->json(['success' => false, 'message' => "Fichier trop volumineux (max: {$maxSizeText})"], 400);
        }

        try {

            $existingProof = ChallengeProof::byChallenge($defiId)
                ->byRoom($userRoomId)
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
            Log::error('Erreur lors du téléversement du défi: ' . $e->getMessage());
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
            $user = User::with('room')->findOrFail($id);

            $userRoomId = $user->getRoomId();

            $defiId = $validated['defiId'];
            $proof = ChallengeProof::byChallenge($defiId)
                ->byRoom($userRoomId)
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
            Log::error('Erreur lors de la suppression de la preuve de défi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }
}
