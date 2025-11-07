<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PushToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'device_name',
        'active',
        'last_used_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope pour récupérer uniquement les tokens actifs
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Marquer le token comme utilisé
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
