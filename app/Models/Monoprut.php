<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Room;

class Monoprut extends Model
{
    use HasFactory;

    protected $table = 'monoprut';

    protected $fillable = ['product', 'quantity', 'type', 'giver_room_id', 'receiver_room_id'];

    public function giver()
    {
        return $this->belongsTo(Room::class, 'giver_room_id');
    }

    public function receiver()
    {
        return $this->belongsTo(Room::class, 'receiver_room_id');
    }

    public function isGiverRoom(User $user)
    {
        return $this->giver_room_id == $user->room()->id;
    }

    public function isReceiverRoom(User $user)
    {
        return $this->receiver_room_id == $user->roomID;
    }
}