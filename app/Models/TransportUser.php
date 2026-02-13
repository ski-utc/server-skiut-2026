<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransportUser extends Model
{
    use HasFactory;

    protected $table = 'transport_user';
    protected $fillable = [
        'user_id',
        'transport_id',
    ];

    /**
     * Get the user that belongs to the transport user.
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the transport that belongs to the transport user.
     *
     * @return BelongsTo
     */
    public function transport()
    {
        return $this->belongsTo(Transport::class, 'transport_id');
    }
}
