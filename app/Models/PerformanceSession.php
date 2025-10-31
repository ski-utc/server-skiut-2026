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
        'speed_history',
        'accuracy'
    ];

    protected $casts = [
        'max_speed' => 'float',
        'average_speed' => 'float',
        'distance' => 'float',
        'duration' => 'integer',
        'speed_history' => 'array',
        'accuracy' => 'float'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
