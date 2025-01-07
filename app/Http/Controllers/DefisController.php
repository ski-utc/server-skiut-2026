<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeProof;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

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

    public function getValidatedProofs($challengeId)
    {
        try {
            // Vérifier si le défi existe
            $challenge = Challenge::findOrFail($challengeId);

            // Récupérer les proofs validées pour ce défi
            $validatedProofs = ChallengeProof::where('challenge_id', $challengeId)
                ->where('valid', true)
                ->get(['id', 'file', 'user_id', 'nb_likes', 'created_at']);

            // Ajouter l'URL complète pour chaque fichier
            $validatedProofs->each(function ($proof) {
                $proof->file_url = url('storage/' . $proof->file); // Utilise 'url' pour générer l'URL publique
            });

            return response()->json([
                'success' => true,
                'data' => $validatedProofs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue : ' . $e->getMessage(),
            ], 500);
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

            // Charger l'utilisateur avec sa chambre associée
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
                'file' => 'required|mimetypes:video/mp4,video/quicktime,image/jpeg,image/png|max:10240',
                'challenge_id' => 'required|exists:challenges,id',
            ]);

            // Génération d'un nom de fichier personnalisé
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension() ?: 'mp4'; // Par défaut mp4
            $fileName = "defi-{$validated['challenge_id']}-user-{$user->id}.{$extension}";

            // Validation du type MIME
            $mimeType = $file->getMimeType();
            if (!in_array($mimeType, ['video/mp4', 'video/quicktime', 'image/jpeg', 'image/png'])) {
                return response()->json(['success' => false, 'message' => 'Type de fichier non supporté'], 400);
            }

            // Enregistrement du fichier
            $filePath = $file->storeAs('challenge_proofs', $fileName, 'public');

            // Vérifier si une preuve existe déjà pour cet utilisateur et ce défi
            $proof = ChallengeProof::where('user_id', $user->id)
                ->where('challenge_id', $validated['challenge_id'])
                ->first();

            if ($proof) {
                // Mettre à jour la preuve existante
                $proof->update([
                    'file' => $filePath,
                    'room_id' => $user->room->id,
                    'nb_likes' => 0,             // Réinitialiser le nombre de likes
                    'valid' => false,
                    'alert' => false,
                    'delete' => false,
                    'active' => true,
                ]);

                $message = 'Défi mis à jour avec succès.';
            } else {
                // Créer une nouvelle preuve si elle n'existe pas
                $proof = ChallengeProof::create([
                    'file' => $filePath,
                    'user_id' => $user->id,
                    'challenge_id' => $validated['challenge_id'],
                    'room_id' => $user->room->id,
                    'nb_likes' => 0,
                    'valid' => false,
                    'alert' => false,
                    'delete' => false,
                    'active' => true,
                ]);

                $message = 'Défi soumis avec succès.';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
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
     * Delete a proof and its associated media.
     */
    public function deleteProof(Request $request, $proofId)
    {
        try {
            // Récupérer la preuve à supprimer
            $proof = ChallengeProof::findOrFail($proofId);

            // Supprimer le fichier associé dans le stockage
            if (Storage::exists('public/' . $proof->file)) {
                Storage::delete('public/' . $proof->file);
            }

            // Supprimer la preuve de la base de données
            $proof->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proof deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
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
