<?php

namespace App\Filament\Resources\PartialRoomShotgunResource\Pages;

use App\Filament\Resources\PartialRoomShotgunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPartialRoomShotgun extends EditRecord
{
    protected static string $resource = PartialRoomShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
