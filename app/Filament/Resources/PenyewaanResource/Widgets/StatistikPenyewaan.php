<?php

namespace App\Filament\Resources\PenyewaanResource\Widgets;

use App\Models\Penyewaan;
use App\Models\Transaksi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatistikPenyewaan extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Penyewaan Masuk', Penyewaan::query()->where('status', 'Pending')->count()),
            Stat::make('Penyewaan Dikonfirmasi', Penyewaan::query()->where('status', 'Confirmed')->count()),
            Stat::make('Total Pemasukan', "Rp " . number_format(Transaksi::query()->where('status', 'paid')->sum('sub_total'), 2, ',', '.'))
        ];
    }
}
