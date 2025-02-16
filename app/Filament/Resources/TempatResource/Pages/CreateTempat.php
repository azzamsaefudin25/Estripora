<?php

namespace App\Filament\Resources\TempatResource\Pages;

use App\Filament\Resources\TempatResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTempat extends CreateRecord
{
    protected static string $resource = TempatResource::class;

    protected static ?string $title = 'Tambah Tempat ';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
