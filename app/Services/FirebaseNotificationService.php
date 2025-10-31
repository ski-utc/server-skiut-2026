<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    public function sendNotification($userIds, $title, $message, $data = [])
    {
        try {
            // Récupérer les tokens des utilisateurs
            $tokens = PushToken::whereIn('user_id', $userIds)
                              ->whereNotNull('token')
                              ->pluck('token')
                              ->unique()
                              ->values()
                              ->toArray();

            if (empty($tokens)) {
                return ['success' => false, 'message' => 'Aucun token trouvé'];
            }

            $results = [];
            $chunks = array_chunk($tokens, 100); // Expo limite à 100 tokens par requête

            foreach ($chunks as $tokenChunk) {
                $payload = [
                    'to' => $tokenChunk,
                    'title' => $title,
                    'body' => $message,
                    'data' => $data,
                    'sound' => 'default',
                    'priority' => 'high',
                    'channelId' => 'default'
                ];

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])->post('https://exp.host/--/api/v2/push/send', $payload);

                $results[] = [
                    'tokens' => count($tokenChunk),
                    'response' => $response->json(),
                    'status' => $response->status()
                ];

                // Log de debug
                Log::info('Firebase notification sent', [
                    'tokens_count' => count($tokenChunk),
                    'title' => $title,
                    'response_status' => $response->status(),
                    'response_body' => $response->json()
                ]);
            }

            return [
                'success' => true,
                'message' => 'Notifications envoyées',
                'results' => $results,
                'total_tokens' => count($tokens)
            ];

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification Firebase', [
                'error' => $e->getMessage(),
                'userIds' => $userIds,
                'title' => $title
            ]);

            return [
                'success' => false,
                'message' => 'Erreur: ' . $e->getMessage()
            ];
        }
    }

    public function sendToAllUsers($title, $message, $data = [])
    {
        $userIds = User::pluck('id')->toArray();
        return $this->sendNotification($userIds, $title, $message, $data);
    }

    public function sendToRooms($roomIds, $title, $message, $data = [])
    {
        $userIds = User::whereIn('roomID', $roomIds)->pluck('id')->toArray();
        return $this->sendNotification($userIds, $title, $message, $data);
    }

    public function sendToAdmins($title, $message, $data = [])
    {
        $adminIds = User::where('admin', true)->pluck('id')->toArray();
        return $this->sendNotification($adminIds, $title, $message, $data);
    }

    public function sendToMembers($title, $message, $data = [])
    {
        // Supposant qu'on ajoute une colonne 'member' plus tard pour les permanences
        // Pour l'instant, on envoie aux non-admins
        $memberIds = User::where('admin', false)->pluck('id')->toArray();
        return $this->sendNotification($memberIds, $title, $message, $data);
    }
}
