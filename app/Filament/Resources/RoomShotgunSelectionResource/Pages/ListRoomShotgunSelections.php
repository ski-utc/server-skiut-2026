<?php

namespace App\Filament\Resources\RoomShotgunSelectionResource\Pages;

use App\Filament\Resources\RoomShotgunSelectionResource;
use Filament\Resources\Pages\ListRecords;

class ListRoomShotgunSelections extends ListRecords
{
    protected static string $resource = RoomShotgunSelectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
