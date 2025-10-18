<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShotgunChambresAdmin extends Model
{
    protected $table = 'shotgun_chambres_admin';

    protected $fillable = [
        'email',
    ];

    public static function isAdmin(string $email): bool
    {
        return self::where('email', $email)->exists();
    }
}
