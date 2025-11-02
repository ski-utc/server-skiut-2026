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

    public function binomes()
    {
        return $this->hasMany(TourBinome::class);
    }

    /**
     * Scope pour récupérer la tournée active
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour récupérer la tournée d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->where('tour_date', Carbon::today());
    }

    /**
     * Récupère la tournée d'aujourd'hui (active ou non)
     */
    public static function getTodayTour()
    {
        return self::today()
                  ->with(['binomes.member1', 'binomes.member2', 'binomes.visits'])
                  ->first();
    }

    /**
     * Récupère la tournée active d'aujourd'hui
     */
    public static function getTodayActiveTour()
    {
        return self::today()
                  ->active()
                  ->with(['binomes.member1', 'binomes.member2', 'binomes.visits'])
                  ->first();
    }

    /**
     * Vérifie si la tournée est en cours
     */
    public function isInProgress()
    {
        return $this->is_active && $this->tour_date->isToday();
    }

    /**
     * Démarre la tournée (l'active)
     */
    public function start()
    {
        self::where('is_active', true)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        $this->update(['is_active' => true]);
    }

    /**
     * Arrête la tournée
     */
    public function stop()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Récupère le statut global de la tournée
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
