<?php

namespace App\Filament\Resources\TempatResource\Pages;

use App\Filament\Resources\TempatResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTempat extends EditRecord
{
    protected static string $resource = TempatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
