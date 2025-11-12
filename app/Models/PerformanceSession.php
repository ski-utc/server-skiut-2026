<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceSession extends Model
{
    use HasFactory;

    protected $table = 'performance_sessions';
    protected $fillable = [
        'user_id',
        'session_id',
        'max_speed',
        'average_speed',
        'distance',
        'duration',
        'session_date',
    ];
    protected $casts = [
        'max_speed' => 'float',
        'average_speed' => 'float',
        'distance' => 'float',
        'duration' => 'integer',
        'session_date' => 'datetime',
    ];

    /**
     * Get the user that owns the performance session.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate average speed from distance and duration.
     *
     * @return float
     */
    public function calculateAverageSpeed()
    {
        if ($this->duration > 0) {
            return ($this->distance / $this->duration) * 3600; // km/h
        }
        return 0;
    }

    /**
     * Scope a query for sessions by user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $user_id
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByUser($query, $user_id)
    {
        return $query->where('user_id', $user_id);
    }

    /**
     * Scope a query for recent sessions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('session_date', '>=', now()->subDays($days));
    }

    /**
     * Scope a query ordered by session date.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $direction
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrderByDate($query, $direction = 'desc')
    {
        return $query->orderBy('session_date', $direction);
    }
}
