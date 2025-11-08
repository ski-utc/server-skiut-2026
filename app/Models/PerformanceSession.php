<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceSession extends Model
{
    use HasFactory;

    protected $table = 'performance_sessions';
    protected $fillable = [
        'user_performance_id',
        'session_id',
        'max_speed',
        'average_speed',
        'distance',
        'duration',
        'session_date',
        'speed_history',
        'accuracy'
    ];
    protected $casts = [
        'max_speed' => 'float',
        'average_speed' => 'float',
        'distance' => 'float',
        'duration' => 'integer',
        'session_date' => 'datetime',
        'speed_history' => 'array',
        'accuracy' => 'float'
    ];

    /**
     * Get the user performance that owns the performance session.
     *
     * @return BelongsTo
     */
    public function userPerformance()
    {
        return $this->belongsTo(UserPerformance::class, 'user_performance_id');
    }

    /**
     * Get the user that owns the performance session.
     *
     * @return User
     */
    public function user()
    {
        return $this->userPerformance->user;
    }
}
