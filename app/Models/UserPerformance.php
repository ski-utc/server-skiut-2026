<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPerformance extends Model
{
    use HasFactory;

    protected $table = 'users_performances';

    protected $fillable = [
        'user_id',
        'max_speed',
        'total_distance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
