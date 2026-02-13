<?php

namespace App\Filament\Resources\PartialRoomShotgunResource\Pages;

use App\Filament\Resources\PartialRoomShotgunResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPartialRoomShotgun extends ViewRecord
{
    protected static string $resource = PartialRoomShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
