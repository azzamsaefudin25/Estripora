<?php

namespace App\Filament\Resources\PenyewaanResource\Pages;

use App\Filament\Resources\PenyewaanResource;
use App\Filament\Resources\PenyewaanResource\Widgets\StatistikPenyewaan;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListPenyewaans extends ListRecords
{
    protected static string $resource = PenyewaanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Penyewaan'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StatistikPenyewaan::class
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'penyewaan_masuk' => Tab::make('Penyewaan Masuk')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Pending')),
            'penyewaan_dikonfirmasi' => Tab::make('Penyewaan Dikonfirmasi')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Confirmed')),
            'penyewaan_dibatalkan' => Tab::make('Penyewaan Dibatalkan')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Canceled')),
        ];
    }
}
