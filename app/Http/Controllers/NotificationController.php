<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use App\Models\UserNotification;
use App\Notifications\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get the notifications for the connected user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getNotifications(Request $request)
    {
        try {
            $user_id = $request->user['id'];

            $user = User::findOrFail($user_id);

            $generalNotifications = Notification::displayed()
                ->global()
                ->orderBy('created_at', 'desc');

            $targetedNotifications = Notification::displayed()
                ->forUser($user_id)
                ->orderBy('created_at', 'desc');

            $roomNotifications = Notification::displayed()
                ->forRoom($user->getRoomId())
                ->orderBy('created_at', 'desc');

            $allNotifications = $generalNotifications->get()
                ->merge($targetedNotifications->get())
                ->merge($roomNotifications->get())
                ->sortByDesc('created_at')
                ->values();

            $data = $allNotifications->map(function ($notification) use ($user_id) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'created_at' => $notification->created_at,
                    'read' => $notification->isReadBy($user_id),
                    'read_at' => $notification->userNotifications()->where('user_id', $user_id)->first()?->read_at ?? null,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des notifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get all the notifications for the admin
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getAdminNotifications()
    {
        try {
            $notifications = Notification::with('sender')
                ->orderBy('created_at', 'desc')
                ->get();

            $data = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'general' => $notification->general,
                    'display' => $notification->display,
                    'push_sent' => $notification->push_sent,
                    'target_users' => $notification->target_users,
                    'target_rooms' => $notification->target_rooms,
                    'sender' => $notification->sender ? $notification->sender->firstName . ' ' . $notification->sender->lastName : null,
                    'created_at' => $notification->created_at,
                    'recipients_count' => $this->getRecipientsCount($notification)
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des notifications pour l\'admin: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the details of a specific notification by its ID
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getNotificationDetails($notificationId)
    {
        try {
            $notification = Notification::findOrFail($notificationId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'general' => $notification->general,
                    'display' => $notification->display,
                    'push_sent' => $notification->push_sent,
                    'target_users' => $notification->target_users,
                    'target_rooms' => $notification->target_rooms,
                    'sender_id' => $notification->sender_id,
                    'created_at' => $notification->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des détails de la notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la notification : ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create and send a new notification
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function createNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:global,targeted,room_based',
                'target_users' => 'nullable|array',
                'target_users.*' => 'exists:users,id',
                'target_rooms' => 'nullable|array',
                'target_rooms.*' => 'exists:rooms,id',
                'send_push' => 'boolean',
                'display' => 'boolean'
            ]);

            $recipientIds = $this->getRecipientIds((object) [
                'type' => $validated['type'],
                'target_users' => $validated['target_users'] ?? null,
                'target_rooms' => $validated['target_rooms'] ?? null,
            ]);

            $notification = Notification::createWithRecipients([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'sender_id' => $request->user['id'],
                'type' => $validated['type'],
                'target_users' => $validated['target_users'] ?? null,
                'target_rooms' => $validated['target_rooms'] ?? null,
                'general' => $validated['type'] === 'global',
                'display' => $validated['display'] ?? true,
            ], $recipientIds);

            if ($validated['send_push'] ?? true) {
                $users = User::whereIn('id', $recipientIds)->get();
                $successCount = 0;
                $failureCount = 0;

                foreach ($users as $user) {
                    try {
                        $user->notify(new NewNotification([
                            'id' => $notification->id,
                            'title' => $validated['title'],
                            'content' => $validated['description'],
                        ]));
                        $successCount++;
                    } catch (\Exception $e) {
                        $failureCount++;
                        Log::warning('Failed to send notification to user', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $notification->update([
                    'push_sent' => $successCount > 0,
                    'firebase_response' => [
                        'success' => $successCount > 0,
                        'success_count' => $successCount,
                        'failure_count' => $failureCount,
                        'total_recipients' => count($recipientIds),
                    ]
                ]);

                Log::info('Push notifications sent', [
                    'notification_id' => $notification->id,
                    'success' => $successCount,
                    'failure' => $failureCount,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification créée et envoyée',
                'data' => [
                    'recipients_count' => count($recipientIds)
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création de la notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Mark a notification as read
     * @param Request $request
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $validated = $request->validate([
            'read' => 'required|boolean',
        ]);

        try {
            $user_id = $request->user['id'];
            $notification = Notification::findOrFail($notificationId);

            $success = $notification->markAsReadBy($user_id, $validated['read']);

            if (!$success) {
                return response()->json(['success' => false, 'message' => 'Notification non trouvée'], 404);
            }

            $message = $validated['read'] ? 'Notification marquée comme lue' : 'Notification marquée comme non lue';
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Toggle the display status for the admin
     * @param Request $request
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function toggleDisplay(Request $request, $notificationId)
    {
        try {
            $notification = Notification::findOrFail($notificationId);
            $notification->update([
                'display' => !$notification->display
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour',
                'display' => $notification->display
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du statut de la notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete a notification (admin)
     * @param Request $request
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function deleteNotification(Request $request, $notificationId)
    {
        try {
            $notification = Notification::findOrFail($notificationId);

            UserNotification::where('notification_id', $notificationId)->delete();

            $notification->delete();

            return response()->json(['success' => true, 'message' => 'Notification supprimée']);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la notification: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the list of users and rooms for the admin interface
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getRecipientsData()
    {
        try {
            $users = User::select('id', 'firstName', 'lastName', 'room_id', 'admin')
                ->orderBy('firstName')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->firstName . ' ' . $user->lastName,
                        'room_id' => $user->room_id,
                        'admin' => $user->admin
                    ];
                });

            $rooms = Room::select('id', 'roomNumber', 'name')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'rooms' => $rooms
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des destinataires: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper: Get the recipient IDs according to the notification type
     * @param Notification $notification
     * @return array
     */
    private function getRecipientIds($notification)
    {
        switch ($notification->type) {
            case 'global':
                return User::pluck('id')->toArray();

            case 'targeted':
                return $notification->target_users ?? [];

            case 'room_based':
                return User::whereIn('room_id', $notification->target_rooms ?? [])->pluck('id')->toArray();

            default:
                return [];
        }
    }

    /**
     * Helper: Count the number of recipients
     * @param Notification $notification
     * @return int
     */
    private function getRecipientsCount($notification)
    {
        return count($this->getRecipientIds($notification));
    }
}
