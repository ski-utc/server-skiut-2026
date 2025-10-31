<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPerformance extends Model
{
    use HasFactory;

    protected $table = 'users_performances';

    protected $fillable = ['user_id', 'max_speed', 'total_distance', 'duration', 'average_speed', 'session_id', 'session_date'];

    protected $casts = [
        'max_speed' => 'float',
        'total_distance' => 'float',
        'duration' => 'integer',
        'average_speed' => 'float',
        'session_date' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
