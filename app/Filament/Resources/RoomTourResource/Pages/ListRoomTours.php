<?php

namespace App\Filament\Resources\RoomTourResource\Pages;

use App\Filament\Resources\RoomTourResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomTours extends ListRecords
{
    protected static string $resource = RoomTourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
