<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Send a notification to a list of users.
     *
     * @param array $userIds List of user IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array
     */
    public function sendNotification($userIds, $title, $message, $data = [])
    {
        try {
            $tokens = PushToken::whereIn('user_id', $userIds)
                              ->where('active', true)
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

    /**
     * Send a notification to all users.
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array
     */
    public function sendToAllUsers($title, $message, $data = [])
    {
        $userIds = User::pluck('id')->toArray();
        return $this->sendNotification($userIds, $title, $message, $data);
    }

    /**
     * Send a notification to a list of rooms.
     *
     * @param array $roomIds List of room IDs
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array
     */
    public function sendToRooms($roomIds, $title, $message, $data = [])
    {
        $userIds = User::whereIn('room_id', $roomIds)->pluck('id')->toArray();
        return $this->sendNotification($userIds, $title, $message, $data);
    }

    /**
     * Send a notification to all admins.
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array
     */
    public function sendToAdmins($title, $message, $data = [])
    {
        $adminIds = User::where('admin', true)->pluck('id')->toArray();
        return $this->sendNotification($adminIds, $title, $message, $data);
    }

    /**
     * Send a notification to all members.
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $data Additional data
     * @return array
     */
    public function sendToMembers($title, $message, $data = [])
    {
        $memberIds = User::where('member', true)->pluck('id')->toArray();
        return $this->sendNotification($memberIds, $title, $message, $data);
    }
}
