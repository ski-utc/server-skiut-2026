<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    use HasFactory;

    protected $table = 'transports';
    protected $fillable = [
        'id',
        'departure',
        'arrival',
        'colour',
        'colourName',
        'type',
        'horaire_depart',
        'horaire_arrivee',
    ];

    /**
     * Get the users that belong to the transport.
     *
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'transport_user', 'transport_id', 'user_id');
    }

    /**
     * Get the departure time attribute.
     *
     * @param string $value
     * @return string
     */
    public function getHoraireDepartAttribute($value)
    {
        return date('H:i:s', strtotime($value));
    }

    /**
     * Get the arrival time attribute.
     *
     * @param string $value
     * @return string
     */
    public function getHoraireArriveeAttribute($value)
    {
        return date('H:i:s', strtotime($value));
    }

    /**
     * Set the departure time attribute.
     *
     * @param string $value
     */
    public function setHoraireDepartAttribute($value)
    {
        $this->attributes['horaire_depart'] = date('H:i:s', strtotime($value));
    }

    /**
     * Set the arrival time attribute.
     *
     * @param string $value
     */
    public function setHoraireArriveeAttribute($value)
    {
        $this->attributes['horaire_arrivee'] = date('H:i:s', strtotime($value));
    }
}
