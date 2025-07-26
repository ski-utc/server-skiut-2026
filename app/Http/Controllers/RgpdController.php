<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use App\Models\User;
use App\Models\Anecdote;
use App\Models\UserPerformance;
use App\Models\PushToken;
use App\Models\ChallengeProof;
use App\Models\Room;
use App\Models\SkinderLike;
use App\Models\AnecdotesLike;
use App\Models\AnecdotesWarn;
use Carbon\Carbon;

class RgpdController extends Controller
{
    /**
     * Anonymise les données d'un.e utilisateur.ice spécifique
     */
    public function anonymizeMyData(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            DB::beginTransaction();

            // Anonymiser l'utilisateur
            $user->update([
                'firstName' => 'Utilisateur',
                'lastName' => 'Anonymisé',
                'email' => 'anonyme_' . $userId . '@etu.utc.fr',
                'cas' => 'anonyme_' . $userId,
                'location' => null
            ]);

            // Anonymiser les anecdotes
            Anecdote::where('userId', $userId)->update([
                'text' => 'Contenu anonymisé'
            ]);

            // Anonymiser les performances
            UserPerformance::where('user_id', $userId)->delete();

            // Supprimer les tokens push
            PushToken::where('user_id', $userId)->delete();

            // Anonymiser les preuves de défis
            $proofs = ChallengeProof::where('user_id', $userId)->get();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                $proof->delete();
            }

            // Anonymiser la chambre si l'utilisateur en est responsable
            $room = Room::where('userID', $userId)->first();
            if ($room) {
                $room->update([
                    'name' => 'Chambre anonymisée',
                    'description' => 'Description anonymisée',
                    'passions' => json_encode([])
                ]);

                // Supprimer la photo de la chambre
                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                    $room->update(['photoPath' => null]);
                }
            }

            // Supprimer les likes Skinder
            SkinderLike::where('room_likeur', $user->roomID)->delete();
            SkinderLike::where('room_liked', $user->roomID)->delete();

            // Supprimer les likes d'anecdotes
            AnecdotesLike::where('user_id', $userId)->delete();

            // Supprimer les avertissements d'anecdotes
            AnecdotesWarn::where('user_id', $userId)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vos données ont été anonymisées avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'anonymisation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime toutes les données d'un.e utilisateur.ice spécifique
     */
    public function deleteMyData(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            DB::beginTransaction();

            // Supprimer les preuves de défis et leurs fichiers
            $proofs = ChallengeProof::where('user_id', $userId)->get();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }
            ChallengeProof::where('user_id', $userId)->delete();

            // Supprimer la chambre et sa photo si l'utilisateur en est responsable
            $room = Room::where('userID', $userId)->first();
            if ($room) {
                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                $room->delete();
            }

            // Supprimer toutes les données liées
            Anecdote::where('userId', $userId)->delete();
            UserPerformance::where('user_id', $userId)->delete();
            PushToken::where('user_id', $userId)->delete();
            SkinderLike::where('room_likeur', $user->roomID)->delete();
            SkinderLike::where('room_liked', $user->roomID)->delete();
            AnecdotesLike::where('user_id', $userId)->delete();
            AnecdotesWarn::where('user_id', $userId)->delete();

            // Supprimer l'utilisateur
            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vos données ont été supprimées avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupère un zip avec toutes les données d'un.e utilisateur.ice
     */
    public function exportMyData(Request $request)
    {
        try {
            $userId = $request->user['id'];
            $user = User::with(['anecdotes', 'performances', 'room'])->find($userId);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            // Créer un dossier temporaire pour les données
            $tempDir = storage_path('app/temp/user_' . $userId . '_' . time());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Créer le fichier texte avec les données
            $dataContent = $this->formatUserData($user);
            file_put_contents($tempDir . '/mes_donnees.txt', $dataContent);

            // Copier les photos
            $photosDir = $tempDir . '/photos';
            if (!file_exists($photosDir)) {
                mkdir($photosDir, 0755, true);
            }

            // Copier la photo de la chambre
            if ($user->room && $user->room->photoPath) {
                $relativePath = str_replace('storage/', '', $user->room->photoPath);
                $roomPhotoPath = storage_path('app/public/' . $relativePath);
                if (file_exists($roomPhotoPath)) {
                    copy($roomPhotoPath, $photosDir . '/photo_chambre.jpg');
                }
            }

            // Copier les photos des preuves de défis
            $proofs = ChallengeProof::where('user_id', $userId)->get();
            foreach ($proofs as $index => $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    $proofPath = storage_path('app/public/' . $relativePath);
                    if (file_exists($proofPath)) {
                        $extension = pathinfo($proofPath, PATHINFO_EXTENSION);
                        copy($proofPath, $photosDir . '/preuve_defi_' . ($index + 1) . '.' . $extension);
                    }
                }
            }

            // Créer le zip
            $zipPath = storage_path('app/temp/mes_infos_' . Carbon::now()->format('Y-m-d-H-i-s') . '.zip');
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
                $this->addFolderToZip($zip, $tempDir, '');
                $zip->close();

                // Nettoyer le dossier temporaire
                $this->deleteDirectory($tempDir);

                // Retourner le fichier zip
                return response()->download($zipPath)->deleteFileAfterSend(true);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du fichier zip'
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anonymise toutes les données de tou.te.s les utilisateur.ice.s (nécessite la clé SiMDE)
     */
    public function anonymizeAllData(Request $request)
    {
        try {
            // Vérifier la clé SiMDE
            $simdeKey = $request->input('simde_key');
            if ($simdeKey !== env('SIMDE_KEY')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé d\'autorisation invalide'
                ], 403);
            }

            DB::beginTransaction();

            // Anonymiser tou.te.s les utilisateur.ice.s
            $users = User::all();
            foreach ($users as $user) {
                $user->update([
                    'firstName' => 'Utilisateur',
                    'lastName' => 'Anonymisé',
                    'email' => 'anonyme_' . $user->id . '@etu.utc.fr',
                    'cas' => 'anonyme_' . $user->id,
                    'location' => null
                ]);
            }

            // Anonymiser toutes les anecdotes
            Anecdote::query()->update(['text' => 'Contenu anonymisé']);

            // Supprimer toutes les performances
            UserPerformance::query()->delete();

            // Supprimer tous les tokens push
            PushToken::query()->delete();

            // Supprimer toutes les preuves de défis et leurs fichiers
            $proofs = ChallengeProof::all();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }
            ChallengeProof::query()->delete();

            // Anonymiser toutes les chambres
            Room::query()->update([
                'name' => 'Chambre anonymisée',
                'description' => 'Description anonymisée',
                'passions' => json_encode([])
            ]);

            // Supprimer toutes les photos de chambres
            $rooms = Room::all();
            foreach ($rooms as $room) {
                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                    $room->update(['photoPath' => null]);
                }
            }

            // Supprimer tous les likes et avertissements
            SkinderLike::query()->delete();
            AnecdotesLike::query()->delete();
            AnecdotesWarn::query()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Toutes les données ont été anonymisées avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'anonymisation globale : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprime toutes les données de tou.te.s les utilisateur.ice.s (nécessite la clé SiMDE)
     */
    public function deleteAllData(Request $request)
    {
        try {
            // Vérifier la clé SiMDE
            $simdeKey = $request->input('simde_key');
            if ($simdeKey !== env('SIMDE_KEY')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé d\'autorisation invalide'
                ], 403);
            }

            DB::beginTransaction();

            // Supprimer toutes les preuves de défis et leurs fichiers
            $proofs = ChallengeProof::all();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // Supprimer toutes les photos de chambres
            $rooms = Room::all();
            foreach ($rooms as $room) {
                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // Supprimer toutes les données
            User::query()->delete();
            Anecdote::query()->delete();
            UserPerformance::query()->delete();
            PushToken::query()->delete();
            ChallengeProof::query()->delete();
            Room::query()->delete();
            SkinderLike::query()->delete();
            AnecdotesLike::query()->delete();
            AnecdotesWarn::query()->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Toutes les données ont été supprimées avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression globale : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Formate les données utilisateur.ice pour l'export
     */
    private function formatUserData($user)
    {
        $content = "=== DONNÉES PERSONNELLES ===\n";
        $content .= "ID: " . $user->id . "\n";
        $content .= "Prénom: " . $user->firstName . "\n";
        $content .= "Nom: " . $user->lastName . "\n";
        $content .= "Email: " . $user->email . "\n";
        $content .= "CAS: " . $user->cas . "\n";
        $content .= "Chambre ID: " . $user->roomID . "\n";
        $content .= "Localisation: " . ($user->location ?? 'Non renseignée') . "\n";
        $content .= "Admin: " . ($user->admin ? 'Oui' : 'Non') . "\n";
        $content .= "Alumni/Externe: " . ($user->alumniOrExte ? 'Oui' : 'Non') . "\n";
        $content .= "Date de création: " . $user->created_at . "\n";
        $content .= "Dernière modification: " . $user->updated_at . "\n\n";

        // Données de la chambre
        if ($user->room) {
            $content .= "=== DONNÉES DE LA CHAMBRE ===\n";
            $content .= "ID Chambre: " . $user->room->id . "\n";
            $content .= "Numéro: " . $user->room->roomNumber . "\n";
            $content .= "Capacité: " . $user->room->capacity . "\n";
            $content .= "Nom: " . $user->room->name . "\n";
            $content .= "Description: " . $user->room->description . "\n";
            $content .= "Passions: " . $user->room->passions . "\n";
            $content .= "Points totaux: " . $user->room->totalPoints . "\n\n";
        }

        // Anecdotes
        if ($user->anecdotes->count() > 0) {
            $content .= "=== ANECDOTES ===\n";
            foreach ($user->anecdotes as $anecdote) {
                $content .= "ID: " . $anecdote->id . "\n";
                $content .= "Texte: " . $anecdote->text . "\n";
                $content .= "Chambre: " . $anecdote->room . "\n";
                $content .= "Valide: " . ($anecdote->valid ? 'Oui' : 'Non') . "\n";
                $content .= "Active: " . ($anecdote->active ? 'Oui' : 'Non') . "\n";
                $content .= "Date: " . $anecdote->created_at . "\n\n";
            }
        }

        // Performances
        if ($user->performances) {
            $content .= "=== PERFORMANCES ===\n";
            $content .= "Vitesse max: " . $user->performances->max_speed . "\n";
            $content .= "Distance totale: " . $user->performances->total_distance . "\n\n";
        }

        // Preuves de défis
        $proofs = ChallengeProof::where('user_id', $user->id)->get();
        if ($proofs->count() > 0) {
            $content .= "=== PREUVES DE DÉFIS ===\n";
            foreach ($proofs as $proof) {
                $content .= "ID: " . $proof->id . "\n";
                $content .= "Fichier: " . $proof->file . "\n";
                $content .= "Défi ID: " . $proof->challenge_id . "\n";
                $content .= "Chambre ID: " . $proof->room_id . "\n";
                $content .= "Valide: " . ($proof->valid ? 'Oui' : 'Non') . "\n";
                $content .= "Date: " . $proof->created_at . "\n\n";
            }
        }

        return $content;
    }

    /**
     * Ajoute un dossier au zip
     */
    private function addFolderToZip($zip, $folder, $relativePath)
    {
        $files = scandir($folder);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $folder . '/' . $file;
                $zipPath = $relativePath . '/' . $file;
                
                if (is_dir($filePath)) {
                    $this->addFolderToZip($zip, $filePath, $zipPath);
                } else {
                    $zip->addFile($filePath, $zipPath);
                }
            }
        }
    }

    /**
     * Supprime un dossier et son contenu (récursif)
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
} 