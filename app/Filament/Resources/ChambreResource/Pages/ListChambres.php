<?php

namespace App\Filament\Resources\ChambreResource\Pages;

use App\Filament\Resources\ChambreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListChambres extends ListRecords
{
    protected static string $resource = ChambreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
