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
        'type',
        'horaire_depart',
        'horaire_arrivee',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'transport_user', 'transport_id', 'user_id');
    }

    public function getHoraireDepartAttribute($value)
    {
        return date('H:i:s', strtotime($value));
    }

    public function getHoraireArriveeAttribute($value)
    {
        return date('H:i:s', strtotime($value));
    }

    public function setHoraireDepartAttribute($value)
    {
        $this->attributes['horaire_depart'] = date('H:i:s', strtotime($value));
    }

    public function setHoraireArriveeAttribute($value)
    {
        $this->attributes['horaire_arrivee'] = date('H:i:s', strtotime($value));
    }
}
