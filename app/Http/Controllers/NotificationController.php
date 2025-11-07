<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseNotificationService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    /**
     * Récupère les notifications pour l'utilisateur connecté
     */
    public function getNotifications(Request $request)
    {
        try {
            $user_id = $request->user['id'];

            // Récupérer les notifications générales affichées
            $generalNotifications = Notification::where('display', true)
                ->where(function ($query) {
                    $query->where('type', 'global')
                          ->orWhere('general', true);
                })
                ->orderBy('created_at', 'desc');

            // Récupérer les notifications ciblées pour cet utilisateur
            $targetedNotifications = Notification::where('display', true)
                ->where('type', 'targeted')
                ->whereJsonContains('target_users', $user_id)
                ->orderBy('created_at', 'desc');

            // Récupérer les notifications par chambre
            $user = User::find($user_id);
            $roomNotifications = Notification::where('display', true)
                ->where('type', 'room_based')
                ->whereJsonContains('target_rooms', $user->room_id)
                ->orderBy('created_at', 'desc');

            $allNotifications = $generalNotifications->get()
                ->merge($targetedNotifications->get())
                ->merge($roomNotifications->get())
                ->sortByDesc('created_at')
                ->values();

            $data = $allNotifications->map(function ($notification) use ($user_id) {
                $userNotification = UserNotification::where('user_id', $user_id)
                    ->where('notification_id', $notification->id)
                    ->first();

                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'type' => $notification->type,
                    'created_at' => $notification->created_at,
                    'read' => $userNotification ? $userNotification->read : false,
                    'read_at' => $userNotification ? $userNotification->read_at : null,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Récupère toutes les notifications pour l'admin
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
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Créer et envoyer une nouvelle notification
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

            $notification = Notification::create([
                'title' => $validated['title'],
                'description' => $validated['description'],
                'sender_id' => $request->user['id'],
                'type' => $validated['type'],
                'target_users' => $validated['target_users'] ?? null,
                'target_rooms' => $validated['target_rooms'] ?? null,
                'general' => $validated['type'] === 'global',
                'display' => $validated['display'] ?? true,
            ]);

            // Déterminer les destinataires
            $recipientIds = $this->getRecipientIds($notification);

            // Créer les entrées UserNotification
            foreach ($recipientIds as $recipientId) {
                UserNotification::create([
                    'user_id' => $recipientId,
                    'notification_id' => $notification->id,
                    'read' => false
                ]);
            }

            // Envoyer les notifications push si demandé
            if ($validated['send_push'] ?? true) {
                $pushResult = $this->firebaseService->sendNotification(
                    $recipientIds,
                    $validated['title'],
                    $validated['description'],
                    ['notificationId' => $notification->id]
                );

                $notification->update([
                    'push_sent' => $pushResult['success'],
                    'firebase_response' => $pushResult
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification créée et envoyée',
                'data' => $notification,
                'recipients_count' => count($recipientIds)
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Request $request, $notificationId)
    {
        try {
            $user_id = $request->user['id'];

            $userNotification = UserNotification::where('user_id', $user_id)
                ->where('notification_id', $notificationId)
                ->first();

            if (!$userNotification) {
                return response()->json(['success' => false, 'message' => 'Notification non trouvée']);
            }

            $userNotification->update([
                'read' => true,
                'read_at' => now()
            ]);

            return response()->json(['success' => true, 'message' => 'Notification marquée comme lue']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle display status pour admin
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
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Supprimer une notification (admin)
     */
    public function deleteNotification(Request $request, $notificationId)
    {
        try {
            $notification = Notification::findOrFail($notificationId);

            // Supprimer les entrées de liaison
            UserNotification::where('notification_id', $notificationId)->delete();

            // Supprimer la notification
            $notification->delete();

            return response()->json(['success' => true, 'message' => 'Notification supprimée']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtenir la liste des utilisateurs et chambres pour l'interface admin
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

            $rooms = Room::select('id', 'name')->orderBy('name')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'users' => $users,
                    'rooms' => $rooms
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }

    /**
     * Helper: Obtenir les IDs des destinataires selon le type de notification
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
     * Helper: Compter le nombre de destinataires
     */
    private function getRecipientsCount($notification)
    {
        return count($this->getRecipientIds($notification));
    }
}
