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
    protected $fillable = [
        'token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Check if the token is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the token is valid.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return !$this->isExpired();
    }
}
