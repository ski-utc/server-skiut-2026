<?php

namespace App\Http\Controllers;

use App\Models\Challenge;
use App\Models\ChallengeProof;
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
                    if ($proof->valid) {
                        $status = 'done';
                    } else {
                        $status = 'waiting';
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
            $proof = ChallengeProof::where('challenge_id',$defiId)->where('room_id',$userRoomId)->first();

            Log::error($proof);

            if(!$proof){
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
                    'file'=>'storage/' . $filePath,
                    'challenge_id'=>$defiId,
                    'room_id'=>$userRoomId,
                    'user_id'=>$id
                ]
            );
    
            return response()->json(['success' => true, 'message' => 'Défi envoyé avec succès !']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du téléversement du défi : ' . $e->getMessage()], 500);
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
        try {
            $id = $request->user['id'];

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
