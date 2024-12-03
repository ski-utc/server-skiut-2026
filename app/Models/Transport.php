<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transport extends Model
{
    /** @use HasFactory<\Database\Factories\TransportFactory> */
    use HasFactory;

    protected $table = 'transport';
    protected $fillable = ['id', 'departure', 'arrival', 'colour', 'type']; 

    // Define the many-to-many relationship with User
    public function users()
    {
        return $this->belongsToMany(User::class, 'transport_user');
    }
}
