<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Récupère les notifications
     */
    public function getNotifications()
    {
        try {
            $notifications = Notification::where('display', true)
            ->orderBy('created_at', 'desc')
            ->get();

            $data = $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'description' => $notification->description,
                    'created_at' => $notification->created_at,
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
        }
    }
}
