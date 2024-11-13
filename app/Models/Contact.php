<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    /** @use HasFactory<\Database\Factories\ContactFactory> */
    use HasFactory;

    protected $keyType = 'string';
    public $incrementing = false;
    
    protected $table = 'contacts';
    protected $fillable = ['id', 'name', 'role', 'phoneNumber']; 
}
