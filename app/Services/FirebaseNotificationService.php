<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    private $expoApiUrl = 'https://exp.host/--/api/v2/push/send';

    /**
     * Send a notification to a list of users using Expo Push Service.
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

            Log::info('Sending notifications via Expo Push Service', [
                'tokens_count' => count($tokens),
                'title' => $title,
            ]);

            $result = $this->sendViaExpo($tokens, $title, $message, $data);

            return [
                'success' => $result['success'],
                'message' => 'Notifications envoyées via Expo',
                'results' => $result,
                'total_tokens' => count($tokens),
                'success_count' => $result['success_count'] ?? 0,
                'failure_count' => $result['failure_count'] ?? 0,
            ];

        } catch (\Exception $e) {
            Log::error('Erreur envoi notification', [
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
     * Send notifications via Expo Push Service
     *
     * @param array $tokens
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    private function sendViaExpo($tokens, $title, $message, $data = [])
    {
        try {
            // Split into batches of 100 (Expo limit)
            $batches = array_chunk($tokens, 100);
            $allResults = [];
            $totalSuccess = 0;
            $totalFailure = 0;

            foreach ($batches as $batchTokens) {
                $messages = [];

                foreach ($batchTokens as $token) {
                    if (!str_starts_with($token, 'ExponentPushToken[') && !str_starts_with($token, 'ExpoPushToken[')) {
                        Log::warning('Skipping invalid Expo token format', ['token' => substr($token, 0, 30)]);
                        continue;
                    }

                    $messages[] = [
                        'to' => $token,
                        'title' => $title,
                        'body' => $message,
                        'data' => $data,
                        'sound' => 'default',
                        'priority' => 'high',
                        'channelId' => 'default',
                    ];
                }

                if (empty($messages)) {
                    continue;
                }

                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Accept-Encoding' => 'gzip, deflate',
                    'Content-Type' => 'application/json',
                ])->post($this->expoApiUrl, $messages);

                $responseData = $response->json();

                if (isset($responseData['data'])) {
                    foreach ($responseData['data'] as $index => $result) {
                        if ($result['status'] === 'ok') {
                            $totalSuccess++;
                        } else {
                            $totalFailure++;

                            $errorDetails = $result['details'] ?? [];
                            $errorMessage = $errorDetails['error'] ?? 'Unknown error';

                            if ($errorMessage !== 'DeviceNotRegistered') {
                                Log::warning('Expo push error', [
                                    'error' => $errorMessage,
                                    'token' => substr($messages[$index]['to'] ?? 'unknown', 0, 30),
                                ]);
                            }

                            if (in_array($errorMessage, ['DeviceNotRegistered', 'InvalidCredentials'])) {
                                $this->deactivateToken($messages[$index]['to'] ?? null);
                            }
                        }
                    }
                }

                $allResults[] = $responseData;
            }

            Log::info('Expo notifications batch completed', [
                'total_tokens' => count($tokens),
                'success' => $totalSuccess,
                'failure' => $totalFailure,
            ]);

            return [
                'success' => true,
                'tokens_count' => count($tokens),
                'success_count' => $totalSuccess,
                'failure_count' => $totalFailure,
                'responses' => $allResults,
            ];

        } catch (\Exception $e) {
            Log::error('Expo push error', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'tokens_count' => count($tokens),
                'success_count' => 0,
                'failure_count' => count($tokens),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Deactivate invalid token
     *
     * @param string|null $token
     * @return void
     */
    private function deactivateToken($token)
    {
        if (!$token) {
            return;
        }

        try {
            PushToken::where('token', $token)
                ->update(['active' => false]);

            Log::info('Deactivated invalid token', [
                'token' => substr($token, 0, 30)
            ]);
        } catch (\Exception $e) {
            Log::error('Error deactivating token', ['error' => $e->getMessage()]);
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
