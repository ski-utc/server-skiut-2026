<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\User;
use App\Models\SkinderLike;
use Illuminate\Support\Facades\Storage;

class SkinderController extends Controller
{
    public function getProfilSkinder(Request $request)
    {
        try {
            $userId = $request->user['id'];;
            $roomId = User::where('id',$userId)->first()->roomID;

            $photoPath = Room::where('id',$roomId)->first()->photoPath;
            $relativePath = str_replace('storage/', '', $photoPath);

            if (!$photoPath || !Storage::disk('public')->exists($relativePath)) {
                return response()->json(['success' => false, 'message' => "NoPhoto"]);
            }

            $room = Room::whereNotIn('id', function ($query) use ($roomId) {
                $query->select('room_liked')
                      ->from('skinder_likes')
                      ->where('room_likeur', $roomId);
            })
            ->whereNot('id', $roomId)
            ->whereNotNull('photoPath')
            ->inRandomOrder()
            ->first();

            if (!$room) {
                return response()->json(['success' => false, 'message' => "TooMuch"]);
            }
    
            return response()->json([
                'success' => true,
                'data' => [
                    'id'=>$room->id,
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

    public function likeSkinder(Request $request)
    {
        $userId = $request->user['id'];;
        $roomLikeur = User::where('id',$userId)->first()->roomID;

        $roomLiked = $request->input('roomLiked');

        if($roomLikeur == $roomLiked) {
            return response()->json(['success' => false, 'message' => "Une chambre ne peut pas s'auto_liker"]);
        }

        SkinderLike::firstOrCreate([
            'room_likeur' => $roomLikeur,
            'room_liked' => $roomLiked,
        ]);

        $reverseLike = SkinderLike::where('room_likeur', $roomLiked)
            ->where('room_liked', $roomLikeur)
            ->exists();

        if($reverseLike){
            $otherRoom = Room::where('id',$roomLiked)->first();
            $otherRoomResp = User::where('id', $otherRoom->userID)->first();
            return response()->json([
                'success' => true,
                'match' => $reverseLike,
                'myRoomImage'=>asset(Room::where('id',$roomLikeur)->first()->photoPath),
                'otherRoomImage'=>asset($otherRoom->photoPath),
                'otherRoomNumber'=>$otherRoom->roomNumber,
                'respRoom' => $otherRoomResp ? $otherRoomResp->firstName . ' ' . $otherRoomResp->lastName : null
            ]);
        } else {
            return response()->json([
                'success' => true,
                'match' => $reverseLike,
            ]);
        }
    }

    public function getMySkinderMatches(Request $request)
    {
        try {
            $userId = $request->user['id'];;
            $roomId = User::where('id', $userId)->first()->roomID;
    
            $matchedRooms = SkinderLike::where('room_likeur', $roomId)
                ->whereIn('room_liked', function ($query) use ($roomId) {
                    $query->select('room_likeur')
                          ->from('skinder_likes')
                          ->where('room_liked', $roomId);
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
                $room = Room::find($like->room_liked);
                $user = User::find($room->userID);
    
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

    public function getMyProfilSkinder(Request $request)
    {
        $userId = $request->user['id'];;
        $roomId = User::where('id',$userId)->first()->roomID;

        $room = Room::findOrFail($roomId);

        if (!$room) {
            return response()->json(['success' => false, 'message' => 'Erreur lors de la récupération de la chambre']);
        }

        return response()->json([
            'success'=>true,
            'data' => [
                'id'=>$room->id,
                'name' => $room->name,
                'description' => $room->description,
                'image' => asset($room->photoPath),
                'passions' => json_decode($room->passions, true) ?? [],
            ]
        ]);
    }

    public function modifyProfil(Request $request)
    {
        $userId = $request->user['id'];
        $roomId = User::where('id',$userId)->first()->roomID;

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

    public function uploadRoomImage(Request $request)
    {
        $userId = $request->user['id'];
        $roomId = User::where('id', $userId)->first()->roomID;
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
    
}
