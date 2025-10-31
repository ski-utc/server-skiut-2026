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
        'additional_members',
        'location',
        'status',
        'notification_sent',
        'notes'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'additional_members' => 'array',
        'notification_sent' => 'boolean'
    ];

    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function getAllMembers()
    {
        $memberIds = array_merge(
            [$this->responsible_user_id],
            $this->additional_members ?? []
        );

        return User::whereIn('id', array_unique($memberIds))->get();
    }

    public function isUserInvolved($userId)
    {
        return $this->responsible_user_id == $userId ||
               in_array($userId, $this->additional_members ?? []);
    }

    public function getDurationInMinutes()
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    public function shouldNotify()
    {
        return !$this->notification_sent &&
               $this->status === 'scheduled' &&
               $this->start_datetime->subHour()->isPast();
    }

    // Scope pour récupérer les permanences d'un utilisateur
    public function scopeForUser($query, $userId)
    {
        return $query->where('responsible_user_id', $userId)
                    ->orWhereJsonContains('additional_members', $userId);
    }

    // Scope pour récupérer les permanences d'une période
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }
}
