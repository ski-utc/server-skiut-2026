<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackOfficeAdmin extends Model
{
    protected $table = 'back_office_admin';

    protected $fillable = [
        'email',
    ];

    public static function isAdmin(string $email): bool
    {
        return self::where('email', $email)->exists();
    }
}
