<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-information-circle';
    protected static string $view = 'filament.pages.dashboard';
    protected static ?string $title = '';
    protected static ?string $navigationLabel = 'Tableau de bord';

    public static function canAccess(): bool
    {
        return true;
    }
}
