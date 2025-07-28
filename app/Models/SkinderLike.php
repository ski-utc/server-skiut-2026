<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkinderLike extends Model
{
    use HasFactory;

    protected $table = 'skinder_likes';
    protected $fillable = ['room_likeur', 'room_liked'];
}
