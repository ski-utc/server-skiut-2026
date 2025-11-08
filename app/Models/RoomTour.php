<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomTour extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_date',
        'is_active'
    ];
    protected $casts = [
        'tour_date' => 'date',
        'is_active' => 'boolean'
    ];

    /**
     * Get the binomes that belong to the room tour.
     *
     * @return HasMany
     */
    public function binomes()
    {
        return $this->hasMany(TourBinome::class);
    }

    /**
     * Scope to get the active room tours.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get the today's room tours.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeToday($query)
    {
        return $query->where('tour_date', Carbon::today());
    }

    /**
     * Get the today's room tour.
     *
     * @return RoomTour
     */
    public static function getTodayTour()
    {
        return self::today()
                  ->with(['binomes.member1', 'binomes.member2', 'binomes.visits'])
                  ->first();
    }

    /**
     * Get the today's active room tour.
     *
     * @return RoomTour
     */
    public static function getTodayActiveTour()
    {
        return self::today()
                  ->active()
                  ->with(['binomes.member1', 'binomes.member2', 'binomes.visits'])
                  ->first();
    }

    /**
     * Check if the room tour is in progress.
     *
     * @return bool
     */
    public function isInProgress()
    {
        return $this->is_active && $this->tour_date->isToday();
    }

    /**
     * Start the room tour.
     */
    public function start()
    {
        self::where('is_active', true)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }

    /**
     * Stop the room tour.
     */
    public function stop()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get the progress statistics for the room tour.
     *
     * @return array
     */
    public function getProgressStats()
    {
        $totalRooms = 0;
        $visitedRooms = 0;
        $binomesCount = $this->binomes->count();

        foreach ($this->binomes as $binome) {
            $stats = $binome->getVisitStats();
            $totalRooms += $stats['total_rooms'];
            $visitedRooms += $stats['visited_rooms'];
        }

        return [
            'binomes_count' => $binomesCount,
            'total_rooms' => $totalRooms,
            'visited_rooms' => $visitedRooms,
            'progress_percentage' => $totalRooms > 0 ? round(($visitedRooms / $totalRooms) * 100) : 0
        ];
    }
}
