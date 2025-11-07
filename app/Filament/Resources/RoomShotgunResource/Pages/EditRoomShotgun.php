<?php

namespace App\Filament\Resources\RoomShotgunResource\Pages;

use App\Filament\Resources\RoomShotgunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRoomShotgun extends EditRecord
{
    protected static string $resource = RoomShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
