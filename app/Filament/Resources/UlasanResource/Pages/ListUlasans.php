<?php

namespace App\Filament\Resources\UlasanResource\Pages;

use App\Filament\Resources\UlasanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ListUlasans extends ListRecords
{
    protected static string $resource = UlasanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Tambah Ulasan'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua'),
            'rating_tinggi' => Tab::make('Rating Tinggi')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('rating', '>=', 4)),
            'rating_rendah' => Tab::make('Rating Rendah')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('rating', '<=', 2)),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('rating')
                ->options([
                    '1' => '⭐ (1)',
                    '2' => '⭐⭐ (2)',
                    '3' => '⭐⭐⭐ (3)',
                    '4' => '⭐⭐⭐⭐ (4)',
                    '5' => '⭐⭐⭐⭐⭐ (5)',
                ])
                ->label('Rating'),

            Filter::make('verified')
                ->label('Status Penyewaan')
                ->query(fn(Builder $query): Builder => $query->whereHas('penyewaan', fn($q) => $q->where('status', 'confirmed')))
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [];
    }

    protected function getDefaultTableSortColumn(): ?string
    {
        return 'created_at';
    }

    protected function getDefaultTableSortDirection(): ?string
    {
        return 'desc';
    }
}
