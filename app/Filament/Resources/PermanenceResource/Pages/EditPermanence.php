<?php

namespace App\Filament\Resources\PermanenceResource\Pages;

use App\Filament\Resources\PermanenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPermanence extends EditRecord
{
    protected static string $resource = PermanenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
