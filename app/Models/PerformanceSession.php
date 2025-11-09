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
}
