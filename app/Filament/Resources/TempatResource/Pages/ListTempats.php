<?php

namespace App\Filament\Resources\TempatResource\Pages;

use App\Filament\Resources\TempatResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTempats extends ListRecords
{
    protected static string $resource = TempatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
