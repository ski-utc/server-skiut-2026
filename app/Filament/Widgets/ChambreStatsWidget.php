<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ChambreStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $totalChambres = Room::count();
        $chambresDisponibles = Room::whereDoesntHave('users')->count();
        $chambresCompletes = Room::whereHas('users', function ($query) {
            $query->havingRaw('COUNT(*) >= rooms.capacity');
        })->count();
        $chambresBloquees = Room::where('locked_until', '>', now())->count();

        return [
            Stat::make('Total des chambres', $totalChambres)
                ->description('Chambres créées')
                ->descriptionIcon('heroicon-m-home')
                ->color('primary'),

            Stat::make('Chambres disponibles', $chambresDisponibles)
                ->description('Prêtes à être réservées')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Chambres complètes', $chambresCompletes)
                ->description('Toutes les places occupées')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Chambres bloquées', $chambresBloquees)
                ->description('En cours de réservation')
                ->descriptionIcon('heroicon-m-lock-closed')
                ->color('danger'),
        ];
    }
}
