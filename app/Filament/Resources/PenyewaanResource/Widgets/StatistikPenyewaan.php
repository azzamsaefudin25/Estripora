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
            Stat::make('Penyewaan Dibatalkan', Transaksi::query()->where('status', 'Canceled')->count()),
            Stat::make('Total Pemasukan', Transaksi::query()->where('status', 'paid')->sum('sub_total'))
        ];
    }
}
