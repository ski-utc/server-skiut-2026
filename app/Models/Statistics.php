<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Statistics extends Model
{
    use HasFactory;
    
    protected $table = 'statistics';
    protected $fillable = ['id', 'maximumSpeed', 'startTime', 'endTime', 'location'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
