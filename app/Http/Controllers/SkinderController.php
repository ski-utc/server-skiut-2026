<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Room;
use App\Models\SkinderLike;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\FirebaseNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SkinderController extends Controller
{
    /**
     * Récupère les données de l'utilisateur pour le profil Skinder
     */
    public function getProfilSkinder(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $roomId = User::where('id', $user_id)->first()->room_id;

            $photoPath = Room::where('id', $roomId)->first()->photoPath;
            $relativePath = str_replace('storage/', '', $photoPath);

            if (!$photoPath || !Storage::disk('public')->exists($relativePath)) {
                return response()->json(['success' => false, 'message' => 'NoPhoto']);
            }

            $room = Room::whereNotIn('id', function ($query) use ($roomId) {
                $query->select('room_liked_id_id')
                      ->from('skinder_likes')
                      ->where('room_liker_id', $roomId);
            })
            ->whereNot('id', $roomId)
            ->whereNotNull('photoPath')
            ->inRandomOrder()
            ->first();

            if (!$room) {
                return response()->json(['success' => false, 'message' => 'TooMuch']);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $room->id,
                    'name' => $room->name,
                    'description' => $room->description,
                    'image' => asset($room->photoPath),
                    'passions' => json_decode($room->passions, true) ?? [],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération du profil : '. $e]);
        }
    }

    /**
     * Like d'une chambre
     */
    public function likeSkinder(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            ;
            $roomLikeur = User::where('id', $user_id)->first()->room_id;

            $roomLiked = $request->input('roomLiked');

            if ($roomLikeur == $roomLiked) {
                return response()->json(['success' => false, 'message' => "Une chambre ne peut pas s'auto_liker"]);
            }

            if ($roomLiked == null) {
                return response()->json(['success' => false, 'message' => "Crée d'abord ton profil pour liker."]);
            }

            SkinderLike::firstOrCreate([
                'room_liker_id' => $roomLikeur,
                'room_liked_id_id' => $roomLiked,
            ]);

            $reverseLike = SkinderLike::where('room_liker_id', $roomLiked)
                ->where('room_liked_id_id', $roomLikeur)
                ->exists();

            if ($reverseLike) {
                $otherRoom = Room::where('id', $roomLiked)->first();
                $otherRoomResp = User::where('id', $otherRoom->user_id)->first();
                $myRoom = Room::where('id', $roomLikeur)->first();

                // Envoyer des notifications aux occupants des deux chambres
                $this->sendMatchNotifications($myRoom, $otherRoom);

                return response()->json([
                    'success' => true,
                    'match' => $reverseLike,
                    'myRoomImage' => asset($myRoom->photoPath),
                    'otherRoomImage' => asset($otherRoom->photoPath),
                    'otherRoomNumber' => $otherRoom->roomNumber,
                    'respRoom' => $otherRoomResp ? $otherRoomResp->firstName . ' ' . $otherRoomResp->lastName : null
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'match' => $reverseLike,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du like : ' . $e->getMessage()]);
        }
    }

    /**
     * Récupère les matchs de l'utilisateur
     */
    public function getMySkinderMatches(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            ;
            $roomId = User::where('id', $user_id)->first()->room_id;

            $matchedRooms = SkinderLike::where('room_liker_id', $roomId)
                ->whereIn('room_liked_id_id', function ($query) use ($roomId) {
                    $query->select('room_liker_id')
                          ->from('skinder_likes')
                          ->where('room_liked_id_id', $roomId);
                })
                ->get();

            if ($matchedRooms->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Aucun match trouvé pour votre chambre'
                ]);
            }

            $result = $matchedRooms->map(function ($like) {
                $room = Room::find($like->room_liked_id);
                $user = User::find($room->user_id);

                return [
                    'roomId' => $room->id,
                    'roomNumber' => $room->roomNumber,
                    'respRoom' => $user ? $user->firstName . ' ' . $user->lastName : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matchs : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Récupère les données du profil Skinder de l'utilisateur
     */
    public function getMyProfilSkinder(Request $request)
    {
        $user_id = $request->user['id'];
        ;
        $roomId = User::where('id', $user_id)->first()->room_id;

        $room = Room::findOrFail($roomId);

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération de la chambre']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'image' => asset($room->photoPath),
                'passions' => json_decode($room->passions, true) ?? [],
            ]
        ]);
    }

    /**
     * Modifier le profil Skinder de l'utilisateur
     */
    public function modifyProfil(Request $request)
    {
        $user_id = $request->user['id'];
        $roomId = User::where('id', $user_id)->first()->room_id;

        $room = Room::findOrFail($roomId);

        $description = $request->input('description');
        if ($description) {
            $room->description = $description;
        }

        $passions = $request->input('passions');
        if ($passions) {
            $room->passions = json_encode($passions);
        }

        $room->save();
        return response()->json(['success' => true, 'message' => 'Profil mis à jour avec succès.']);
    }

    /**
     * Téléversement d'une image pour la chambre
     */
    public function uploadRoomImage(Request $request)
    {
        $user_id = $request->user['id'];
        $roomId = User::where('id', $user_id)->first()->room_id;
        $room = Room::where('id', $roomId)->first();

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Chambre introuvable'], 404);
        }

        if (!$request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'Aucune image fournie'], 400);
        }

        $file = $request->file('image');

        if (!$file->isValid() || !in_array($file->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            return response()->json(['success' => false, 'message' => 'Fichier invalide ou non pris en charge'], 400);
        }

        try {
            $filePath = $file->storeAs('roomImages', "room_{$room->id}.jpg", 'public');
            $room->photoPath = 'storage/' . $filePath;
            $room->save();

            return response()->json(['success' => true, 'message' => 'Image téléversée avec succès']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erreur lors du téléversement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Récupère les détails complets d'une chambre pour le Skinder
     */
    public function getRoomDetails($roomId)
    {
        try {
            $room = Room::find($roomId);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chambre introuvable'
                ], 404);
            }

            $respUser = User::find($room->user_id);
            $likesReceived = SkinderLike::where('room_liked_id_id', $roomId)->count();
            $likesGiven = SkinderLike::where('room_liker_id', $roomId)->count();

            $matches = SkinderLike::where('room_liker_id', $roomId)
                ->whereIn('room_liked_id_id', function ($query) use ($roomId) {
                    $query->select('room_liker_id')
                          ->from('skinder_likes')
                          ->where('room_liked_id_id', $roomId);
                })
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $room->id,
                    'roomNumber' => $room->roomNumber,
                    'name' => $room->name,
                    'description' => $room->description,
                    'mood' => $room->mood,
                    'image' => $room->photoPath ? asset($room->photoPath) : null,
                    'passions' => json_decode($room->passions, true) ?? [],
                    'totalPoints' => $room->totalPoints,
                    'respUser' => $respUser ? [
                        'id' => $respUser->id,
                        'firstName' => $respUser->firstName,
                        'lastName' => $respUser->lastName,
                        'fullName' => $respUser->firstName . ' ' . $respUser->lastName
                    ] : null,
                    'statistics' => [
                        'likesReceived' => $likesReceived,
                        'likesGiven' => $likesGiven,
                        'matches' => $matches
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la chambre : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Envoie des notifications aux occupants des deux chambres qui ont matché
     */
    private function sendMatchNotifications(Room $room1, Room $room2)
    {
        try {
            $room1Occupants = User::where('room_id', $room1->roomNumber)->get();
            $room2Occupants = User::where('room_id', $room2->roomNumber)->get();
            $allOccupants = $room1Occupants->merge($room2Occupants);

            if ($allOccupants->isEmpty()) {
                return;
            }

            $notification = Notification::create([
                'title' => '💕 Nouveau match Skinder !',
                'description' => "Les chambres {$room1->roomNumber} et {$room2->roomNumber} ont matché ! C'est le moment de faire connaissance et de se rencontrer. Bonne chance ! 🎉",
                'sender_id' => null,
                'type' => 'targeted',
                'target_users' => $allOccupants->pluck('id')->toArray(),
                'target_rooms' => [],
                'general' => false,
                'display' => true,
                'push_sent' => true
            ]);

            foreach ($allOccupants as $user) {
                UserNotification::create([
                    'user_id' => $user->id,
                    'notification_id' => $notification->id,
                    'read' => false
                ]);
            }

            // Envoyer les notifications push
            $firebaseService = app(FirebaseNotificationService::class);
            $userIds = $allOccupants->pluck('id')->toArray();

            $firebaseService->sendNotification(
                $userIds,
                '💕 Nouveau match Skinder !',
                "Les chambres {$room1->roomNumber} et {$room2->roomNumber} ont matché ! Venez vous rencontrer ! 🎉",
                [
                    'type' => 'skinder_match',
                    'room1_number' => $room1->roomNumber,
                    'room2_number' => $room2->roomNumber,
                    'notification_id' => $notification->id
                ]
            );

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications de match Skinder: ' . $e->getMessage());
        }
    }

}
