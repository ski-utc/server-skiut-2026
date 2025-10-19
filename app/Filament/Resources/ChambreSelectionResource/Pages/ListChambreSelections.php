<?php

namespace App\Filament\Resources\ChambreSelectionResource\Pages;

use App\Filament\Resources\ChambreSelectionResource;
use Filament\Resources\Pages\ListRecords;

class ListChambreSelections extends ListRecords
{
    protected static string $resource = ChambreSelectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
