<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PartialRoomShotgun extends Model
{
    use HasFactory;

    protected $table = 'partial_room_shotguns';

    protected $fillable = [
        'nb_personas',
        'name',
        'responsable_chambre',
        'ambiance',
        'firstNeighbourChoice',
        'secondNeighbourChoice',
    ];

    /**
     * Get the users that belong to the partial room shotgun.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(UserRoomShotgun::class, 'partial_room_shotgun_id');
    }
}
