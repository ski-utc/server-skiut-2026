<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permanence extends Model
{
    use HasFactory;

    protected $table = 'permanences';

    protected $fillable = [
        'name',
        'description',
        'start_datetime',
        'end_datetime',
        'responsible_user_id',
        'location',
        'status',
        'notified',
        'notes'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'notified' => 'boolean'
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function getDurationInMinutes()
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    public function shouldNotify()
    {
        return !$this->notified &&
               $this->status === 'scheduled' &&
               $this->start_datetime->subHour()->isPast();
    }

    // Scope pour récupérer les permanences d'un utilisateur
    public function scopeForUser($query, $user_id)
    {
        return $query->where('responsible_user_id', $user_id);
    }

    // Scope pour récupérer les permanences d'une période
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }
}
