<?php

namespace App\Services;

use App\Models\PushToken;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class FirebaseNotificationService
{
    private $messaging;
    private $expoApiUrl = 'https://exp.host/--/api/v2/push/send';

    public function __construct()
    {
        try {
            $envPath = env('FIREBASE_CREDENTIALS_PATH', 'app/private/firebase-service-account.json');
            if (file_exists($envPath)) {
                $credentialsPath = $envPath;
            } elseif (file_exists(storage_path($envPath))) {
                $credentialsPath = storage_path($envPath);
            } elseif (file_exists(base_path('storage/' . $envPath))) {
                $credentialsPath = base_path('storage/' . $envPath);
            } else {
                Log::error('Firebase credentials file not found', [
                    'env_path' => $envPath,
                    'tried_paths' => [
                        $envPath,
                        storage_path($envPath),
                        base_path('storage/' . $envPath),
                    ]
                ]);
                $this->messaging = null;
                return;
            }

            Log::info('Attempting to load Firebase credentials from: ' . $credentialsPath);

            $factory = (new Factory())->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();

            Log::info('Firebase Messaging initialized successfully');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Firebase Messaging: ' . $e->getMessage(), [
                'exception_class' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            $this->messaging = null;
        }
    }

    /**
     * Send a notification to a list of users using FCM v1 API or Expo Push Service.
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
                ->get();

            if ($tokens->isEmpty()) {
                return ['success' => false, 'message' => 'Aucun token trouvé'];
            }

            $expoTokens = [];
            $fcmTokens = [];

            foreach ($tokens as $tokenModel) {
                $token = $tokenModel->token;

                if (str_starts_with($token, 'ExponentPushToken[') || str_starts_with($token, 'ExpoPushToken[')) {
                    $expoTokens[] = $token;
                } else {
                    $fcmTokens[] = $token;
                }
            }

            $results = [];

            if (!empty($expoTokens)) {
                $expoResult = $this->sendViaExpo($expoTokens, $title, $message, $data);
                $results['expo'] = $expoResult;
            }

            if (!empty($fcmTokens) && $this->messaging) {
                $fcmResult = $this->sendViaFCM($fcmTokens, $title, $message, $data);
                $results['fcm'] = $fcmResult;
            }

            $totalTokens = count($expoTokens) + count($fcmTokens);
            $totalSuccess = ($results['expo']['success_count'] ?? 0) + ($results['fcm']['success_count'] ?? 0);
            $totalFailure = ($results['expo']['failure_count'] ?? 0) + ($results['fcm']['failure_count'] ?? 0);

            Log::info('Notifications sent', [
                'expo_tokens' => count($expoTokens),
                'fcm_tokens' => count($fcmTokens),
                'total_success' => $totalSuccess,
                'total_failure' => $totalFailure,
            ]);

            return [
                'success' => true,
                'message' => 'Notifications envoyées',
                'results' => $results,
                'total_tokens' => $totalTokens,
                'success_count' => $totalSuccess,
                'failure_count' => $totalFailure,
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
            $messages = [];

            foreach ($tokens as $token) {
                $messages[] = [
                    'to' => $token,
                    'title' => $title,
                    'body' => $message,
                    'data' => $data,
                    'sound' => 'default',
                    'priority' => 'high',
                ];
            }

            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Accept-Encoding' => 'gzip, deflate',
                'Content-Type' => 'application/json',
            ])->post($this->expoApiUrl, $messages);

            $responseData = $response->json();

            $successCount = 0;
            $failureCount = 0;

            if (isset($responseData['data'])) {
                foreach ($responseData['data'] as $result) {
                    if ($result['status'] === 'ok') {
                        $successCount++;
                    } else {
                        $failureCount++;
                        if (isset($result['details']['error'])) {
                            Log::warning('Expo push error: ' . $result['details']['error']);
                        }
                    }
                }
            }

            Log::info('Expo notifications sent', [
                'tokens_count' => count($tokens),
                'success' => $successCount,
                'failure' => $failureCount,
            ]);

            return [
                'success' => true,
                'tokens_count' => count($tokens),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'response' => $responseData,
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
     * Send notifications via FCM v1 API
     *
     * @param array $tokens
     * @param string $title
     * @param string $message
     * @param array $data
     * @return array
     */
    private function sendViaFCM($tokens, $title, $message, $data = [])
    {
        if (!$this->messaging) {
            Log::error('FCM Messaging not initialized');
            return [
                'success' => false,
                'tokens_count' => count($tokens),
                'success_count' => 0,
                'failure_count' => count($tokens),
                'error' => 'FCM not initialized',
            ];
        }

        try {
            $notification = Notification::create($title, $message);

            $successCount = 0;
            $failureCount = 0;
            $errors = [];

            foreach ($tokens as $token) {
                try {
                    $fcmMessage = CloudMessage::withTarget('token', $token)
                        ->withNotification($notification)
                        ->withData($data);

                    $this->messaging->send($fcmMessage);
                    $successCount++;
                } catch (\Exception $e) {
                    $failureCount++;
                    $errors[] = [
                        'token' => substr($token, 0, 20) . '...',
                        'error' => $e->getMessage()
                    ];
                    Log::warning('FCM send error for token', [
                        'token' => substr($token, 0, 20) . '...',
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('FCM notifications sent', [
                'tokens_count' => count($tokens),
                'success' => $successCount,
                'failure' => $failureCount,
            ]);

            return [
                'success' => true,
                'tokens_count' => count($tokens),
                'success_count' => $successCount,
                'failure_count' => $failureCount,
                'errors' => $errors,
            ];

        } catch (\Exception $e) {
            Log::error('FCM batch error', ['error' => $e->getMessage()]);
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
