<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Penyewaan;
use App\Models\Transaksi;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\TransaksiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransaksiResource\RelationManagers;

class TransaksiResource extends Resource
{
    protected static ?string $model = Transaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function getNavigationLabel(): string
    {
        return 'Transaksi'; 
    }
    public static function getPluralLabel(): string
    {
        return 'Transaksi'; 
    }
    public static function getModelLabel(): string
    {
        return 'Transaksi';
    }

    public static function getnavigationGroup(): ?string
    {
        return 'Sewa & Keuangan';
    }


    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canEdit(Model $record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canDelete(Model $record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function canView(Model $record): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('nik')
                    ->label('Identitas')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return User::whereHas('penyewaan')
                            ->pluck('name', 'nik')
                            ->mapWithKeys(function ($name, $nik) {
                                return [$nik => "{$nik} - {$name}"];
                            })
                            ->toArray();
                    })
                    ->live()
                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                    ->afterStateUpdated(function ($set, $state) {
                        if (!$state) {
                            $set('id_penyewaan', null);
                            $set('tgl_booking', null);
                            $set('detail_penyewaan', null);
                            $set('total_durasi', null);
                            $set('tarif', null);
                            $set('sub_total', null);
                            $set('penyewaan_options', []);
                            return;
                        }

                        $penyewaanList = Penyewaan::with(['lokasi.tempat'])
                            ->where('nik', $state)
                            ->get()
                            ->mapWithKeys(function ($penyewaan) {
                                $label = $penyewaan->lokasi && $penyewaan->lokasi->tempat
                                    ? "{$penyewaan->id_penyewaan} - {$penyewaan->lokasi->tempat->nama} - {$penyewaan->lokasi->nama_lokasi}"
                                    : "Penyewaan #{$penyewaan->id_penyewaan}";

                                return [$penyewaan->id_penyewaan => $label];
                            })
                            ->toArray();

                        $set('penyewaan_options', $penyewaanList);
                    }),

                TextInput::make('id_billing')
                    ->label('ID Billing')
                    ->required(),

                Select::make('id_penyewaan')
                    ->label('Penyewaan')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(fn(Get $get): array => $get('penyewaan_options') ?? [])
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if (!$state) {
                            $set('tgl_booking', null);
                            $set('detail_penyewaan', null);
                            $set('total_durasi', null);
                            $set('tarif', null);
                            $set('sub_total', null);
                            return;
                        }

                        $penyewaan = Penyewaan::with(['lokasi.tempat', 'user'])
                            ->where('id_penyewaan', $state)
                            ->where('nik', $get('nik'))
                            ->first();

                        if ($penyewaan) {
                            $user = $penyewaan->user;

                            $detailText = [];
                            $detailText[] = "Penyewa: {$user->name} (NIK: {$user->nik})";
                            $detailText[] = "Lokasi: {$penyewaan->lokasi->tempat->nama} - {$penyewaan->lokasi->nama_lokasi}";
                            $detailText[] = "Kategori Sewa: " . ucfirst($penyewaan->kategori_sewa);

                            if ($penyewaan->kategori_sewa === 'per jam') {
                                foreach ($penyewaan->penyewaan_per_jam as $index => $item) {
                                    $detailText[] = sprintf(
                                        "(%d) Tanggal: %s\nJam: %s - %s",
                                        $index + 1,
                                        Carbon::parse($item['tgl_mulai'])->format('d/m/Y'),
                                        $item['jam_mulai'],
                                        $item['jam_selesai']
                                    );
                                }
                            } else {
                                foreach ($penyewaan->penyewaan_per_hari as $index => $item) {
                                    $detailText[] = sprintf(
                                        "(%d) Periode: %s - %s",
                                        $index + 1,
                                        Carbon::parse($item['tgl_mulai'])->format('d/m/Y'),
                                        Carbon::parse($item['tgl_selesai'])->format('d/m/Y')
                                    );
                                }
                            }

                            $set('tgl_booking', $penyewaan->tgl_booking);
                            $set('detail_penyewaan', implode("\n", $detailText));
                            $set('total_durasi', $penyewaan->total_durasi);
                            $set('tarif', $penyewaan->tarif);
                            $set('sub_total', $penyewaan->sub_total);
                        }
                    })
                    ->rules([
                        function (callable $get) {
                            return Rule::unique('transaksi', 'id_penyewaan')
                                ->where(function ($query) {
                                    return $query->whereIn('status', ['Pending', 'Paid']);
                                })
                                ->ignore($get('id'), 'id');
                        },
                    ])
                    ->validationMessages([
                        'unique' => 'Transaksi yang sama sudah dibuat.  Harap cek status transaksi.',
                    ]),

                TextInput::make('tgl_booking')
                    ->label('Tanggal Booking')
                    ->disabled()
                    ->dehydrated(true),

                Textarea::make('detail_penyewaan')
                    ->label('Detail Penyewaan')
                    ->disabled()
                    ->dehydrated(true)
                    ->rows(6),

                TextInput::make('total_durasi')
                    ->label('Total Durasi')
                    ->disabled()
                    ->dehydrated(true)
                    ->suffix(function (Get $get) {
                        $penyewaan = Penyewaan::find($get('id_penyewaan'));
                        if ($penyewaan && isset($penyewaan->kategori_sewa)) {
                            return $penyewaan->kategori_sewa === 'per jam' ? 'Jam' : 'Hari';
                        }
                        return '';
                    }),
                TextInput::make('luas')
                    ->label('Luas'),

                TextInput::make('tarif')
                    ->label('Tarif')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true),
                // ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 2, '.', ',') : null),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true),
                // ->formatStateUsing(fn($state) => $state ? number_format((float)$state, 2, '.', ',') : null),

                Select::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->required()
                    ->options([
                        'Transfer Bank' => 'Transfer Bank',
                        'E-Wallet' => 'E-Wallet',
                        'Kartu Kredit' => 'Kartu Kredit'
                    ]),
                Select::make('status')
                    ->label('Status')
                    ->default('Pending')
                    ->options([
                        'Pending' => 'Pending',
                        'Paid' => 'Paid',
                        'Failed' => 'Failed'
                    ])
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('penyewaan.user.name')
                    ->label('Identitas')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id_penyewaan')
                    ->label('Penyewaan')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {

                        $penyewaan = Penyewaan::with(['lokasi.tempat'])
                            ->where('id_penyewaan', $state)
                            ->first();

                        if ($penyewaan && $penyewaan->lokasi && $penyewaan->lokasi->tempat) {

                            return "{$penyewaan->lokasi->tempat->nama} - {$penyewaan->lokasi->nama_lokasi}";
                        }

                        return $state;
                    }),
                TextColumn::make('id_billing')
                    ->label('ID Billing')
                    ->sortable(),

                TextColumn::make('tgl_booking')
                    ->label('Tanggal Booking')
                    ->sortable(),

                TextColumn::make('detail_penyewaan')
                    ->label('Detail Penyewaan')
                    ->limit(50)
                    ->sortable(),

                TextColumn::make('luas')
                    ->label('Luas')
                    ->sortable(),
                TextColumn::make('tarif')
                    ->label('Tarif')
                    ->formatStateUsing(fn($state) => $state ? 'Rp ' . number_format((float)$state, 2, '.', ',') : null)
                    ->sortable(),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->formatStateUsing(fn($state) => $state ? 'Rp ' . number_format((float)$state, 2, '.', ',') : null)
                    ->sortable(),

                SelectColumn::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->sortable()
                    ->options([
                        'Transfer Bank' => 'Transfer Bank',
                        'E-Wallet' => 'E-Wallet',
                        'Kartu Kredit' => 'Kartu Kredit',
                    ]),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransaksis::route('/'),
            'create' => Pages\CreateTransaksi::route('/create'),
            'edit' => Pages\EditTransaksi::route('/{record}/edit'),
        ];
    }
}
