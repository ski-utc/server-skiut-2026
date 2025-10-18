<?php

namespace App\Filament\Resources\ChambreResource\Pages;

use App\Filament\Resources\ChambreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChambre extends EditRecord
{
    protected static string $resource = ChambreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
