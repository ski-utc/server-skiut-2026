<?php

namespace App\Filament\Resources\ChambreResource\Pages;

use App\Filament\Resources\ChambreResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewChambre extends ViewRecord
{
    protected static string $resource = ChambreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
