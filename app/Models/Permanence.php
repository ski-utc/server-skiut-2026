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

    /**
     * Get the user that is responsible for the permanence.
     *
     * @return BelongsTo
     */
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Get the duration of the permanence in minutes.
     *
     * @return int
     */
    public function getDurationInMinutes()
    {
        return $this->start_datetime->diffInMinutes($this->end_datetime);
    }

    /**
     * Check if the permanence should notify.
     *
     * @return bool
     */
    public function shouldNotify()
    {
        return !$this->notified &&
               $this->status === 'scheduled' &&
               $this->start_datetime->subHour()->isPast();
    }

    /**
     * Scope to get the permanences for a user.
     *
     * @param Builder $query
     * @param int $user_id
     * @return Builder
     */
    public function scopeForUser($query, $user_id)
    {
        return $query->where('responsible_user_id', $user_id);
    }

    /**
     * Scope to get the permanences in a period.
     *
     * @param Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return Builder
     */
    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_datetime', [$startDate, $endDate]);
    }
}
