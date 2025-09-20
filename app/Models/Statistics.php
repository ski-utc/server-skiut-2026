<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    /** @use HasFactory<\Database\Factories\StatisticsFactory> */
    use HasFactory;
    
    protected $table = 'statistics';
    protected $fillable = ['id', 'maximumSpeed', 'startTime', 'endTime', 'location']; // location doit choisir ce qu'on met dedans (quel point ? endroit où est le plus rapide ??)

    // Define the inverse relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
