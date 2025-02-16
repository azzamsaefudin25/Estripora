<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TransaksiResource;

class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Transaksi'),
        ];
    }
    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'transaksi_baru' => Tab::make('Transaksi Baru')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Pending')),
            'transaksi_selesai' => Tab::make('Transaksi Selesai')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Paid')),
        ];
    }
}
