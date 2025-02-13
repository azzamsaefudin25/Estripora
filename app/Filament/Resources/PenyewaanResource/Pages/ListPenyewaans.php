<?php

namespace App\Filament\Resources\PenyewaanResource\Pages;

use App\Filament\Resources\PenyewaanResource;
use App\Filament\Resources\PenyewaanResource\Widgets\StatistikPenyewaan;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPenyewaans extends ListRecords
{
    protected static string $resource = PenyewaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatistikPenyewaan::class
        ];
    }
}
