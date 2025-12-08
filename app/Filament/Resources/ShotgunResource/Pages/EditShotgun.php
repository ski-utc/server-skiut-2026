<?php

namespace App\Filament\Resources\ShotgunResource\Pages;

use App\Filament\Resources\ShotgunResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShotgun extends EditRecord
{
    protected static string $resource = ShotgunResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
