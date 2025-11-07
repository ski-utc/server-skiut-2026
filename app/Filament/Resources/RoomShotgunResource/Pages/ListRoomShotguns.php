<?php

namespace App\Filament\Resources\RoomShotgunResource\Pages;

use App\Filament\Resources\RoomShotgunResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomShotguns extends ListRecords
{
    protected static string $resource = RoomShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
