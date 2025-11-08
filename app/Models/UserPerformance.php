<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPerformance extends Model
{
    use HasFactory;

    protected $table = 'user_performances';
    protected $fillable = [
        'user_id',
        'max_speed',
        'total_distance',
        'duration',
        'average_speed',
        'session_id',
        'session_date'
    ];
    protected $casts = [
        'max_speed' => 'float',
        'total_distance' => 'float',
        'duration' => 'integer',
        'average_speed' => 'float',
        'session_date' => 'datetime'
    ];

    /**
     * Get the user that owns the user performance.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the sessions that belong to the user performance.
     *
     * @return HasMany
     */
    public function sessions()
    {
        return $this->hasMany(PerformanceSession::class, 'user_performance_id');
    }
}
