<?php

namespace App\Filament\Resources\ShotgunResource\Pages;

use App\Filament\Resources\ShotgunResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListShotguns extends ListRecords
{
    protected static string $resource = ShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
