<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShotgunToken extends Model
{
    use HasFactory;

    protected $table = 'shotgun_tokens';
    protected $primaryKey = 'token';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['token', 'expires_at'];
}
