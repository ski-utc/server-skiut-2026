<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChambreUser extends Model
{
    protected $table = 'chambre_user';

    protected $fillable = [
        'chambre_id',
        'email',
    ];

    public function chambre(): BelongsTo
    {
        return $this->belongsTo(Chambre::class);
    }
}
