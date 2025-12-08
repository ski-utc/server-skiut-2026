<?php

namespace App\Filament\Resources\PartialRoomShotgunResource\Pages;

use App\Filament\Resources\PartialRoomShotgunResource;
use Filament\Resources\Pages\ListRecords;

class ListPartialRoomShotguns extends ListRecords
{
    protected static string $resource = PartialRoomShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Pas d'action create car les chambres partielles sont créées par les utilisateurs
        ];
    }
}
