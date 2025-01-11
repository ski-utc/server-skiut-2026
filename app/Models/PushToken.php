<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushToken extends Model
{
    use HasFactory;

    /**
     * Les champs autorisés pour l'attribution massive.
     */
    protected $fillable = [
        'token', 
        'user_id',
    ];
}
