<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Penyewaan;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\PenyewaanResource;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(PenyewaanResource::getEloquentQuery())
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama User')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('lokasi.nama_lokasi')
                    ->label('Lokasi dan Tempat')
                    ->getStateUsing(function ($record) {

                        $lokasi = $record->lokasi;
                        if ($lokasi && $lokasi->tempat) {
                            return $lokasi->tempat->nama . ' - ' . $lokasi->nama_lokasi;
                        }
                        return '';
                    })
                    ->sortable()
                    ->searchable(),

                TextColumn::make('penyewaan')
                    ->label('Detail Penyewaan')
                    ->getStateUsing(function ($record) {
                        if (!$record) return '';

                        $schedules = null;
                        if ($record->kategori_sewa === 'per jam') {
                            $schedules = is_string($record->penyewaan_per_jam)
                                ? json_decode($record->penyewaan_per_jam, true)
                                : $record->penyewaan_per_jam;

                            if (!empty($schedules)) {
                                return nl2br(implode("\n", array_map(function ($item) {
                                    return "Tanggal: {$item['tgl_mulai']}, Jam: {$item['jam_mulai']} - {$item['jam_selesai']}";
                                }, $schedules)));
                            }
                        } elseif ($record->kategori_sewa === 'per hari') {
                            $schedules = is_string($record->penyewaan_per_hari)
                                ? json_decode($record->penyewaan_per_hari, true)
                                : $record->penyewaan_per_hari;

                            if (!empty($schedules)) {
                                return nl2br(implode("\n", array_map(function ($item) {
                                    return "Tanggal: {$item['tgl_mulai']} - {$item['tgl_selesai']}";
                                }, $schedules)));
                            }
                        }

                        return '';
                    })
                    ->html() // Penting agar bisa render HTML
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tarif')
                    ->label('Tarif')
                    ->getStateUsing(function ($record) {
                        $tarif = $record->tarif;

                        // Format tarif menjadi format Rupiah
                        $formattedTarif = 'Rp ' . number_format($tarif, 2, ',', '.');

                        if ($record->kategori_sewa === 'per jam') {
                            // Menampilkan tarif dengan satuan per jam
                            return "{$formattedTarif} / jam";
                        } elseif ($record->kategori_sewa === 'per hari') {
                            // Menampilkan tarif dengan satuan per hari
                            return "{$formattedTarif} / hari";
                        }

                        return '';
                    })
                    ->sortable(),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->getStateUsing(function ($record) {
                        $subTotal = $record->sub_total;
                        $formattedSubTotal = 'Rp ' . number_format($subTotal, 2, ',', '.');

                        return $formattedSubTotal;
                    })
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view')
                    ->icon('heroicon-m-eye')
                    ->url(function ($record) {
                        // Mengarahkan ke halaman penyewaan dengan ID tertentu
                        return PenyewaanResource::getUrl('index', ['record' => $record]);
                    }),
            ]);
    }
}
