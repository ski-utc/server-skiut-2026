<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monoprut extends Model
{
    use HasFactory;

    protected $table = 'monoprut';

    protected $fillable = ['product', 'quantity', 'giver_id', 'receiver_id'];
}