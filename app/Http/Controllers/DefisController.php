<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeProof;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DefisController extends Controller
{
    /**
     * Get all challenges with their proofs.
     */
    public function getChallenges(Request $request)
    {
        $token = $request->bearerToken();

        try {
            $publicKey = config("services.crypt.public");
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            $id = $decoded->key;

            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            $userRoomId = $user->roomID;

            $challenges = Challenge::with('challengeProofs')->get();

            $challengesWithValidation = $challenges->map(function ($challenge) use ($userRoomId) {
                $estValide = $challenge->challengeProofs->where('valid', true)
                    ->filter(function ($proof) use ($userRoomId) {
                        return $proof->user->roomID === $userRoomId;
                    })
                    ->isNotEmpty();

                return [
                    'id' => $challenge->id,
                    'title' => $challenge->title,
                    'nbPoints' => $challenge->nbPoints,
                    'estValide' => $estValide,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $challengesWithValidation,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Invalid Token'], 401);
        }
    }

    public function postProof(Request $request)
    {
        $token = $request->bearerToken();

        try {
            // Décoder le token JWT
            $publicKey = config("services.crypt.public");
            $decoded = JWT::decode($token, new Key($publicKey, 'RS256'));

            $id = $decoded->key;

            // Charger l'utilisateur avec sa salle associée
            $user = User::with('room')->where('id', $id)->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé',
                ], 404);
            }

            if (!$user->room) {
                return response()->json([
                    'success' => false,
                    'message' => "L'utilisateur n'appartient à aucune salle.",
                ], 400);
            }

            // Validation des données de la requête
            $validated = $request->validate([
                'file' => 'required|file|max:10240', // Taille max 10MB
                'challenge_id' => 'required|exists:challenges,id',
            ]);

            // Génération d'un nom de fichier personnalisé
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension(); // Récupère l'extension originale
            $fileName = "defi-{$validated['challenge_id']}-user-{$user->id}.{$extension}";

            // Enregistrement du fichier avec le nouveau nom
            $filePath = $file->storeAs('challenge_proofs', $fileName, 'public');

            // Création de la preuve
            $proof = ChallengeProof::create([
                'file' => $filePath,
                'user_id' => $user->id,
                'challenge_id' => $validated['challenge_id'],
                'room_id' => $user->room->id,
                'nb_likes' => 0,             // Initialisation du nombre de likes
                'valid' => false,
                'alert' => false,
                'delete' => false,
                'active' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Défi soumis avec succès.',
                'data' => $proof,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Validate a proof.
     */
    public function validateProof(Request $request, $proofId)
    {
        $proof = ChallengeProof::findOrFail($proofId);
        $proof->valid = true;
        $proof->save();

        return response()->json([
            'success' => true,
            'message' => 'Proof validated successfully.',
        ]);
    }

    /**
     * Import challenges from a CSV/Excel file.
     */
    public function importChallenges(Request $request)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $file = $request->file('file');

        // Parse and import logic goes here (using a library like Maatwebsite/Excel)

        return response()->json([
            'success' => true,
            'message' => 'Challenges imported successfully.',
        ]);
    }

    /**
     * Get proofs for validation (for admin users).
     */
    public function getProofsForValidation()
    {
        $proofs = ChallengeProof::where('valid', false)->get();

        return response()->json([
            'success' => true,
            'data' => $proofs,
        ]);
    }
}
