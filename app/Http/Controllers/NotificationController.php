<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class NotificationController extends Controller
{
    public function getNotifications()
    {
        try {
            $notifications = Notification::where('delete', false)
            ->orderBy('created_at', 'desc') // Trie par ordre décroissant
            ->get();
    
            $data = $notifications->map(function($notification) {
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
