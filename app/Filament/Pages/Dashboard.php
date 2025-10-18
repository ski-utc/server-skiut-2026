<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = 'Tableau de bord';
    protected static ?string $navigationLabel = 'Tableau de bord';

    public static function canAccess(): bool
    {
        return true;
    }
}
