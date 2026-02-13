<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Room;
use App\Models\SkinderLike;
use App\Models\User;
use App\Notifications\NewNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SkinderController extends Controller
{
    /**
     * Get the user data for the Skinder profile.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getProfilSkinder(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);
            $myRoom = Room::findOrFail($user->getRoomId());

            if (!$myRoom->hasPhoto()) {
                return response()->json(['success' => true, 'message' => 'NoPhoto']);
            }

            $photoPath = $myRoom->photoPath;
            $relativePath = str_replace('storage/', '', $photoPath);

            if (!Storage::disk('public')->exists($relativePath)) {
                return response()->json(['success' => true, 'message' => 'NoPhoto']);
            }

            $room = Room::whereNotIn('id', function ($query) use ($myRoom) {
                $query->select('room_liked_id')
                    ->from('skinder_likes')
                    ->where('room_liker_id', $myRoom->id);
            })
                ->whereNot('id', $myRoom->id)
                ->withPhotos()
                ->inRandomOrder()
                ->first();

            if (!$room) {
                return response()->json(['success' => true, 'message' => 'TooMuch']);
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
            Log::error('Erreur lors de la récupération du profil: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération du profil : ' . $e]);
        }
    }

    /**
     * Like a room.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function likeSkinder(Request $request)
    {
        $validated = $request->validate([
            'roomLiked' => 'required|integer|exists:rooms,id',
        ]);

        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);
            $roomLikeur = $user->getRoomId();
            $roomLiked = $validated['roomLiked'];

            if ($roomLikeur == $roomLiked) {
                return response()->json(['success' => false, 'message' => "Une chambre ne peut pas s'auto_liker"]);
            }

            SkinderLike::firstOrCreate([
                'room_liker_id' => $roomLikeur,
                'room_liked_id' => $roomLiked,
            ]);

            $hasMatch = SkinderLike::hasMatch($roomLikeur, $roomLiked);

            if ($hasMatch) {
                $otherRoom = Room::findOrFail($roomLiked);
                $otherRoomResp = User::find($otherRoom->user_id);
                $myRoom = Room::findOrFail($roomLikeur);

                $this->sendMatchNotifications($myRoom, $otherRoom);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'match' => $hasMatch,
                        'myRoomImage' => asset($myRoom->photoPath),
                        'otherRoomImage' => asset($otherRoom->photoPath),
                        'otherRoomNumber' => $otherRoom->roomNumber,
                        'otherRoomResp' => $otherRoomResp ? $otherRoomResp->firstName . ' ' . $otherRoomResp->lastName : null
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'match' => $hasMatch,
                    ]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors du like: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors du like : ' . $e->getMessage()]);
        }
    }

    /**
     * Get the matches of the user.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getMySkinderMatches(Request $request)
    {
        try {
            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);
            $roomId = $user->getRoomId();

            $matchedRooms = SkinderLike::fromRoom($roomId)
                ->whereIn('room_liked_id', function ($query) use ($roomId) {
                    $query->select('room_liker_id')
                        ->from('skinder_likes')
                        ->where('room_liked_id', $roomId);
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
            Log::error('Erreur lors de la récupération des matchs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des matchs : ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get the data of the user's Skinder profile.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getMyProfilSkinder(Request $request)
    {
        try {
            $user_id = $request->user['id'];

            $roomId = User::where('id', $user_id)->first()->room_id;

            $room = Room::findOrFail($roomId);

            if (!$room) {
                return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération de la chambre'], 404);
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
            Log::error('Erreur lors de la récupération du profil: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération du profil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Modify the user's Skinder profile.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function modifyProfil(Request $request)
    {
        try {
            $validated = $request->validate([
                'description' => 'nullable|string|max:1000',
                'passions' => 'nullable|array',
            ]);

            $user_id = $request->user['id'];
            $user = User::findOrFail($user_id);
            $roomId = $user->getRoomId();

            $room = Room::findOrFail($roomId);

            if (isset($validated['description'])) {
                $room->description = $validated['description'];
            }

            if (isset($validated['passions'])) {
                $room->passions = json_encode($validated['passions']);
            }

            $room->save();
            return response()->json([
                'success' => true,
                'message' => 'Profil mis à jour avec succès.',
                'data' => []
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la modification du profil: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors de la modification du profil: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Upload an image for the room.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function uploadRoomImage(Request $request)
    {
        $validated = $request->validate([
            'media' => 'required|file|mimes:jpeg,png,gif|max:5120',
        ]);

        $user_id = $request->user['id'];
        $user = User::findOrFail($user_id);
        $roomId = $user->getRoomId();
        $room = Room::findOrFail($roomId);

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Chambre introuvable'], 404);
        }

        $file = $request->file('media');

        try {
            $filePath = $file->storeAs('roomImages', "room_{$room->id}.jpg", 'public');
            $room->photoPath = 'storage/' . $filePath;
            $room->save();

            return response()->json([
                'success' => true,
                'message' => 'Image téléversée avec succès',
                'data' => []
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléversement: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erreur lors du téléversement : ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get the details of a room for the Skinder.
     * @param string $roomId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
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
            $likesReceived = SkinderLike::toRoom($roomId)->count();
            $likesGiven = SkinderLike::fromRoom($roomId)->count();

            $matches = SkinderLike::where('room_liker_id', $roomId)
                ->whereIn('room_liked_id', function ($query) use ($roomId) {
                    $query->select('room_liker_id')
                        ->from('skinder_likes')
                        ->where('room_liked_id', $roomId);
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
            Log::error('Erreur lors de la récupération des détails de la chambre: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des détails de la chambre : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notifications to the occupants of the two rooms that have matched.
     * @param Room $room1
     * @param Room $room2
     * @return void
     * @throws \Exception
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

            $notification = Notification::createWithRecipients([
                'title' => '🎉 Nouveau match Skinder ! 🎉',
                'description' => "Les chambres {$room1->roomNumber} et {$room2->roomNumber} ont matché ! C'est le moment de faire connaissance et de se rencontrer. Bonne chance ! 🎉",
                'sender_id' => null,
                'type' => 'targeted',
                'target_users' => $allOccupants->pluck('id')->toArray(),
                'target_rooms' => [],
                'general' => false,
                'display' => true,
                'push_sent' => true
            ], $allOccupants->pluck('id')->toArray());

            foreach ($allOccupants as $user) {
                try {
                    $user->notify(new NewNotification([
                        'id' => $notification->id,
                        'title' => '🎉 Nouveau match Skinder ! 🎉',
                        'content' => "Les chambres {$room1->roomNumber} et {$room2->roomNumber} ont matché ! Venez vous rencontrer !",
                        'data' => [
                            'type' => 'skinder_match',
                            'room1_number' => $room1->roomNumber,
                            'room2_number' => $room2->roomNumber,
                            'notification_id' => $notification->id
                        ]
                    ]));
                } catch (\Exception $e) {
                    Log::warning('Failed to send Skinder match notification', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi des notifications de match Skinder: ' . $e->getMessage());
        }
    }

}
