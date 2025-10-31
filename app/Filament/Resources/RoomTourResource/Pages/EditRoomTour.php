<?php

namespace App\Filament\Resources\RoomTourResource\Pages;

use App\Filament\Resources\RoomTourResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomTour extends EditRecord
{
    protected static string $resource = RoomTourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
