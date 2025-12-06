<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    private $fcmServerKey;

    public function __construct()
    {
        $this->fcmServerKey = env('FIREBASE_SERVER_KEY');

        if (empty($this->fcmServerKey)) {
            Log::warning('FCM Server Key not configured in .env');
        }
    }

    /**
     * Send a notification to a list of users using FCM.
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
            $chunks = array_chunk($tokens, 500);

            foreach ($chunks as $tokenChunk) {
                $payload = [ // FCM Format
                    'registration_ids' => $tokenChunk,
                    'notification' => [
                        'title' => $title,
                        'body' => $message,
                        'sound' => 'default',
                        'priority' => 'high',
                    ],
                    'data' => $data,
                    'priority' => 'high',
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->fcmServerKey,
                    'Content-Type' => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', $payload);

                $responseData = $response->json();

                $results[] = [
                    'tokens' => count($tokenChunk),
                    'response' => $responseData,
                    'status' => $response->status(),
                    'success_count' => $responseData['success'] ?? 0,
                    'failure_count' => $responseData['failure'] ?? 0,
                ];

                Log::info('FCM notification sent', [
                    'tokens_count' => count($tokenChunk),
                    'title' => $title,
                    'response_status' => $response->status(),
                    'success' => $responseData['success'] ?? 0,
                    'failure' => $responseData['failure'] ?? 0,
                ]);
            }

            return [
                'success' => true,
                'message' => 'Notifications envoyées via FCM',
                'results' => $results,
                'total_tokens' => count($tokens)
            ];

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification FCM', [
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
