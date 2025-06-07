<?php

namespace App\Filament\Resources\TransaksiResource\Pages;

use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Resources\TransaksiResource;
use App\Models\Transaksi;
use Filament\Notifications\Notification;

class ListTransaksis extends ListRecords
{
    protected static string $resource = TransaksiResource::class;

    public $notifCount;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Transaksi'),
            
            // Tombol untuk mark all as reviewed
            Actions\Action::make('markAllReviewed')
                ->label('Tandai Semua Sudah Dilihat')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->getNotificationCount() > 0)
                ->requiresConfirmation()
                ->action(function () {
                    // Update semua transaksi pending dengan bukti bayar untuk menandai sudah dilihat
                    $updatedCount = Transaksi::where('status', 'Pending')
                        ->whereNotNull('bukti_bayar')
                        ->whereNull('reviewed_at')
                        ->update(['reviewed_at' => now()]);
                    
                    Notification::make()
                        ->title('Berhasil!')
                        ->body("$updatedCount transaksi telah ditandai sudah dilihat.")
                        ->success()
                        ->send();
                        
                    // Refresh data dan reset table
                    $this->notifCount = $this->getNotificationCount();
                    $this->resetTable();
                }),
        ];
    }

    public function getTabs(): array
    {
        $pendingWithProofCount = Transaksi::where('status', 'Pending')
            ->whereNotNull('bukti_bayar')
            ->whereNull('reviewed_at')
            ->count();

        return [
            'semua' => Tab::make('Semua'),
            'transaksi_baru' => Tab::make('Transaksi Baru')
                ->badge($pendingWithProofCount > 0 ? $pendingWithProofCount : null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Pending')),
            'perlu_review' => Tab::make('Perlu Review')
                ->badge($pendingWithProofCount)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn(Builder $query) => 
                    $query->where('status', 'Pending')
                          ->whereNotNull('bukti_bayar')
                          ->whereNull('reviewed_at')
                ),
            'transaksi_selesai' => Tab::make('Transaksi Selesai')
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', '=', 'Paid')),
        ];
    }

    public function mount(): void
    {
        parent::mount();
        
        // Hitung jumlah transaksi yang perlu review
        $this->notifCount = $this->getNotificationCount();
        
        // Tampilkan notifikasi jika ada transaksi baru dengan bukti bayar
        if ($this->notifCount > 0) {
            Notification::make()
                ->title('⚠️ Transaksi Baru!')
                ->body("Ada {$this->notifCount} transaksi dengan bukti bayar yang perlu direview.")
                ->warning()
                ->duration(10000) // 10 detik
                ->send();
        }
    }
    
    private function getNotificationCount(): int
    {
        return Transaksi::where('status', 'Pending')
            ->whereNotNull('bukti_bayar')
            ->whereNull('reviewed_at')
            ->count();
    }

    // Method untuk auto-refresh setiap 30 detik
    public function getPollingInterval(): ?string
    {
        return null; // Disabled polling
    }

    // Method untuk refresh data manual
    public function refreshData(): void
    {
        $this->notifCount = $this->getNotificationCount();
        $this->resetTable();
        
        Notification::make()
            ->title('Data Diperbarui')
            ->body('Data transaksi telah diperbarui.')
            ->success()
            ->send();
    }

    // Method untuk switch ke tab perlu review
    public function goToReviewTab(): void
    {
        $this->activeTab = 'perlu_review';
        $this->resetTable();
        
        Notification::make()
            ->title('Menampilkan Transaksi yang Perlu Review')
            ->success()
            ->send();
    }
}