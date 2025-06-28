<?php

namespace App\Filament\Resources\LaporResource\Pages;

use App\Filament\Resources\LaporResource;
use App\Models\Lapors;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListLapor extends ListRecords
{
    protected static string $resource = LaporResource::class;

    public int $notifCount = 0;

    //Tab untuk filter laporan
    public function getTabs(): array
    {
        $pendingCount = Lapors::whereNull('balasan')
                            ->orWhere('balasan', '')
                            ->count();

        return [
            'semua' => Tab::make('Semua'),
            'belum' => Tab::make('Belum Dibalas')
                ->badge($pendingCount ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => $query->where(function (Builder $q) {
                    $q->whereNull('balasan')
                    ->orWhere('balasan', '');
                })),
            'sudah' => Tab::make('Sudah Dibalas')
                ->modifyQueryUsing(fn(Builder $query) => $query->whereNotNull('balasan')
                                                        ->where('balasan', '!=', '')),
        ];
    }

    //notifikasi selalu ada jika ada laporan yang belum dibalas
    public function mount(): void
    {
        parent::mount();

        $this->notifCount = Lapors::query()
            ->whereNull('balasan')
            ->orWhere('balasan', '')
            ->count();

        if ($this->notifCount > 0) {
            Notification::make()
                ->title('⚠️ Laporan Baru!')
                ->body("Ada {$this->notifCount} laporan yang belum dibalas.")
                ->warning()
                ->duration(0) // tidak hilang otomatis
                ->send();
        }
    }

    public function getPollingInterval(): ?string
    {
        return null;
    }
}
