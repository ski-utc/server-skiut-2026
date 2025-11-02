<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackOfficeAdmin extends Model
{
    use HasFactory;

    protected $table = 'back_office_admin';

    protected $fillable = [
        'email',
    ];

    public static function isAdmin(string $email): bool
    {
        return self::where('email', $email)->exists();
    }
}
