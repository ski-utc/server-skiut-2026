<?php

namespace App\Http\Controllers;

use App\Models\Anecdote;
use App\Models\AnecdotesLike;
use App\Models\AnecdotesWarn;
use App\Models\ChallengeProof;
use App\Models\PerformanceSession;
use App\Models\Permanence;
use App\Models\PushToken;
use App\Models\Room;
use App\Models\SkinderLike;
use App\Models\TourBinome;
use App\Models\TransportUser;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\UserRoomShotgun;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class RgpdController extends Controller
{
    /**
     * Anonymize the data of a specific user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function anonymizeMyData(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            DB::beginTransaction();

            $user->anonymize();

            Anecdote::where('user_id', $user_id)->update([
                'text' => 'Contenu anonymisé'
            ]);

            PerformanceSession::where('user_id', $user_id)->delete();
            PushToken::where('user_id', $user_id)->delete();
            UserNotification::where('user_id', $user_id)->delete();
            UserRoomShotgun::where('email', $user->email)->delete();
            TransportUser::where('user_id', $user_id)->delete();
            TourBinome::where('member_1_id', $user_id)->orWhere('member_2_id', $user_id)->delete();
            Permanence::where('responsible_user_id', $user_id)->delete();


            $proofs = ChallengeProof::where('user_id', $user_id)->get();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                $proof->delete();
            }

            $room = Room::where('user_id', $user_id)->first();
            if ($room) {
                $room->update([
                    'name' => 'Chambre anonymisée',
                    'description' => 'Description anonymisée',
                    'passions' => json_encode([])
                ]);


                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                    $room->update(['photoPath' => null]);
                }
            }

            SkinderLike::where('room_liker_id', $user->room_id)->delete();
            SkinderLike::where('room_liked_id', $user->room_id)->delete();
            AnecdotesLike::where('user_id', $user_id)->delete();
            AnecdotesWarn::where('user_id', $user_id)->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vos données ont été anonymisées avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'anonymisation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'anonymisation : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all the data of a specific user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteMyData(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::find($user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            DB::beginTransaction();

            $proofs = ChallengeProof::where('user_id', $user_id)->get();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }
            ChallengeProof::where('user_id', $user_id)->delete();

            $room = Room::where('user_id', $user_id)->first();
            if ($room) {
                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
                $room->delete();
            }

            Anecdote::where('user_id', $user_id)->delete();
            PerformanceSession::where('user_id', $user_id)->delete();
            PushToken::where('user_id', $user_id)->delete();
            AnecdotesLike::where('user_id', $user_id)->delete();
            AnecdotesWarn::where('user_id', $user_id)->delete();
            UserNotification::where('user_id', $user_id)->delete();
            UserRoomShotgun::where('email', $user->email)->delete();
            TransportUser::where('user_id', $user_id)->delete();
            TourBinome::where('member_1_id', $user_id)->orWhere('member_2_id', $user_id)->delete();
            Permanence::where('responsible_user_id', $user_id)->delete();

            if ($user->room_id) {
                SkinderLike::where('room_liker_id', $user->room_id)->delete();
                SkinderLike::where('room_liked_id', $user->room_id)->delete();
            }

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vos données ont été supprimées avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la suppression: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a zip with all the data of a user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function exportMyData(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::with(['anecdotes', 'room'])->find($user_id);

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
            }

            $tempDir = storage_path('app/temp/user_' . $user_id . '_' . time());
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $dataContent = $this->formatUserData($user);
            file_put_contents($tempDir . '/mes_donnees.txt', $dataContent);


            $photosDir = $tempDir . '/photos';
            if (!file_exists($photosDir)) {
                mkdir($photosDir, 0755, true);
            }

            if ($user->room && $user->room->photoPath) {
                $relativePath = str_replace('storage/', '', $user->room->photoPath);
                $roomPhotoPath = storage_path('app/public/' . $relativePath);
                if (file_exists($roomPhotoPath)) {
                    copy($roomPhotoPath, $photosDir . '/photo_chambre.jpg');
                }
            }

            $proofs = ChallengeProof::where('user_id', $user_id)->get();
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

            $this->cleanOldZipFiles();

            $zipFilename = 'mes_infos_' . Carbon::now()->format('Y-m-d-H-i-s') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFilename);
            $zip = new ZipArchive();
            if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                $this->addFolderToZip($zip, $tempDir, '');
                $zip->close();

                $this->deleteDirectory($tempDir);

                $response = new BinaryFileResponse($zipPath);
                $response->headers->set('Content-Type', 'application/zip');
                $response->headers->set('Content-Disposition', 'attachment; filename="' . $zipFilename . '"');
                
                $response->deleteFileAfterSend(true);

                return $response;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur lors de la création du fichier zip'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'export: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'export : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Anonymize all the data of all the users (requires the SiMDE key).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function anonymizeAllData(Request $request)
    {
        try {
            $simdeKey = $request->input('simde_key');
            if ($simdeKey !== env('SIMDE_KEY')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé d\'autorisation invalide'
                ], 403);
            }

            DB::beginTransaction();

            $users = User::all();
            foreach ($users as $user) {
                $user->anonymize();
            }

            Anecdote::query()->update(['text' => 'Contenu anonymisé']);
            PerformanceSession::query()->delete();
            PushToken::query()->delete();

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

            $rooms = Room::all();
            foreach ($rooms as $room) {
                $room->update([
                    'name' => 'Chambre anonymisée_'.$room->id,
                    'description' => 'Description anonymisée',
                    'passions' => json_encode([])
                ]);
            }

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
            Log::error('Erreur lors de l\'anonymisation globale: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'anonymisation globale : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete all the data of all the users (requires the SiMDE key).
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteAllData(Request $request)
    {
        try {
            $simdeKey = $request->input('simde_key');
            if ($simdeKey !== env('SIMDE_KEY')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Clé d\'autorisation invalide'
                ], 403);
            }

            DB::beginTransaction();

            $proofs = ChallengeProof::all();
            foreach ($proofs as $proof) {
                if ($proof->file) {
                    $relativePath = str_replace('storage/', '', $proof->file);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            $rooms = Room::all();
            foreach ($rooms as $room) {
                if ($room->photoPath) {
                    $relativePath = str_replace('storage/', '', $room->photoPath);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            User::query()->delete();
            Anecdote::query()->delete();
            PerformanceSession::query()->delete();
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
            Log::error('Erreur lors de la suppression globale: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression globale : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Format the user data for the export.
     * @param User $user
     * @return string
     */
    private function formatUserData($user)
    {
        $content = "=== DONNÉES PERSONNELLES ===\n";
        $content .= 'ID: ' . $user->id . "\n";
        $content .= 'Prénom: ' . $user->firstName . "\n";
        $content .= 'Nom: ' . $user->lastName . "\n";
        $content .= 'Email: ' . $user->email . "\n";
        $content .= 'CAS: ' . $user->cas . "\n";
        $content .= 'Chambre ID: ' . $user->room_id . "\n";
        $content .= 'Admin: ' . ($user->admin ? 'Oui' : 'Non') . "\n";
        $content .= 'Alumni/Externe: ' . ($user->alumniOrExte ? 'Oui' : 'Non') . "\n";
        $content .= 'Date de création: ' . $user->created_at . "\n";
        $content .= 'Dernière modification: ' . $user->updated_at . "\n\n";

        if ($user->room) {
            $content .= "=== DONNÉES DE LA CHAMBRE ===\n";
            $content .= 'ID Chambre: ' . $user->room->id . "\n";
            $content .= 'Numéro: ' . $user->room->roomNumber . "\n";
            $content .= 'Capacité: ' . $user->room->capacity . "\n";
            $content .= 'Nom: ' . $user->room->name . "\n";
            $content .= 'Description: ' . $user->room->description . "\n";
            $content .= 'Passions: ' . $user->room->passions . "\n";
            $content .= 'Points totaux: ' . $user->room->totalPoints . "\n\n";
        }

        if ($user->anecdotes->count() > 0) {
            $content .= "=== ANECDOTES ===\n";
            foreach ($user->anecdotes as $anecdote) {
                $content .= 'ID: ' . $anecdote->id . "\n";
                $content .= 'Texte: ' . $anecdote->text . "\n";
                $content .= 'Chambre: ' . $anecdote->room . "\n";
                $content .= 'Valide: ' . ($anecdote->valid ? 'Oui' : 'Non') . "\n";
                $content .= 'Active: ' . ($anecdote->active ? 'Oui' : 'Non') . "\n";
                $content .= 'Date: ' . $anecdote->created_at . "\n\n";
            }
        }

        $performanceSessions = PerformanceSession::where('user_id', $user->id)->get();
        if ($performanceSessions->count() > 0) {
            $content .= "=== SESSIONS DE PERFORMANCE ===\n";
            $content .= 'Nombre de sessions: ' . $performanceSessions->count() . "\n";
            $content .= 'Vitesse max globale: ' . $performanceSessions->max('max_speed') . " km/h\n";
            $content .= 'Distance totale: ' . $performanceSessions->sum('distance') . " m\n";
            $content .= 'Durée totale: ' . $performanceSessions->sum('duration') . " s\n\n";

            foreach ($performanceSessions as $index => $session) {
                $content .= '--- Session ' . ($index + 1) . " ---\n";
                $content .= 'ID Session: ' . $session->session_id . "\n";
                $content .= 'Vitesse max: ' . $session->max_speed . " km/h\n";
                $content .= 'Vitesse moyenne: ' . $session->average_speed . " km/h\n";
                $content .= 'Distance: ' . $session->distance . " m\n";
                $content .= 'Durée: ' . $session->duration . " s\n";
                $content .= 'Date: ' . $session->created_at . "\n\n";
            }
        }

        $proofs = ChallengeProof::where('user_id', $user->id)->get();
        if ($proofs->count() > 0) {
            $content .= "=== PREUVES DE DÉFIS ===\n";
            foreach ($proofs as $proof) {
                $content .= 'ID: ' . $proof->id . "\n";
                $content .= 'Fichier: ' . $proof->file . "\n";
                $content .= 'Défi ID: ' . $proof->challenge_id . "\n";
                $content .= 'Chambre ID: ' . $proof->room_id . "\n";
                $content .= 'Valide: ' . ($proof->valid ? 'Oui' : 'Non') . "\n";
                $content .= 'Date: ' . $proof->created_at . "\n\n";
            }
        }

        return $content;
    }

    /**
     * Add a folder to the zip.
     * @param ZipArchive $zip
     * @param string $folder
     * @param string $relativePath
     * @return void
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
     * Delete a folder and its content (recursive).
     * @param string $dir
     * @return void
     */
    private function deleteDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
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

    /**
     * Clean old zip files (older than 1 hour) from temp directory.
     * @return void
     */
    private function cleanOldZipFiles()
    {
        $tempPath = storage_path('app/temp');
        if (!is_dir($tempPath)) {
            return;
        }

        $zipFiles = glob($tempPath . '/mes_infos_*.zip');
        $oneHourAgo = time() - 3600;

        foreach ($zipFiles as $file) {
            if (file_exists($file) && filemtime($file) < $oneHourAgo) {
                @unlink($file);
            }
        }
    }
}
