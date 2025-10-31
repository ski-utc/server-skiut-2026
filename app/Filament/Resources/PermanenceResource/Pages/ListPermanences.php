<?php

namespace App\Filament\Resources\PermanenceResource\Pages;

use App\Filament\Resources\PermanenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPermanences extends ListRecords
{
    protected static string $resource = PermanenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
