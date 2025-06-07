<?php
//TransaksiResource.php(filament)
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
use Illuminate\Support\Facades\DB;
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
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\DatePicker;
use App\Filament\Resources\TransaksiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TransaksiResource\RelationManagers;
use Filament\Notifications\Notification;


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
                Select::make('id_penyewaan')
                    ->label('Penyewaan')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(function () {
                        return Penyewaan::with(['lokasi.tempat', 'user'])
                            ->whereIn('status', ['Pending', 'Paid'])
                            ->whereNotExists(function ($query) {
                                $query->select(DB::raw(1))
                                    ->from('transaksi')
                                    ->whereColumn('transaksi.id_penyewaan', 'penyewaan.id_penyewaan')
                                    ->whereIn('transaksi.status', ['Pending', 'Paid']);
                            })
                            ->get()
                            ->mapWithKeys(function ($penyewaan) {
                                $label = $penyewaan->lokasi && $penyewaan->lokasi->tempat
                                    ? "{$penyewaan->id_penyewaan} - {$penyewaan->user->name} - {$penyewaan->lokasi->tempat->nama} - {$penyewaan->lokasi->nama_lokasi}"
                                    : "Penyewaan #{$penyewaan->id_penyewaan} - {$penyewaan->user->name}";

                                return [$penyewaan->id_penyewaan => $label];
                            })
                            ->toArray();
                    })
                    ->live()
                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                    ->afterStateUpdated(function ($set, $state) {
                        if (!$state) {
                            $set('nik', null);
                            $set('tgl_booking', null);
                            $set('detail_penyewaan', null);
                            $set('total_durasi', null);
                            $set('tarif', null);
                            $set('sub_total', null);
                            return;
                        }

                        $penyewaan = Penyewaan::with(['lokasi.tempat', 'user'])
                            ->where('id_penyewaan', $state)
                            ->first();

                        if ($penyewaan) {
                            $user = $penyewaan->user;

                            // Format detail penyewaan sebagai JSON sesuai dengan struktur di Livewire
                            $detailPenyewaan = [
                                'tipe' => $penyewaan->kategori_sewa,
                                'per_hari' => $penyewaan->penyewaan_per_hari ?? [],
                                'per_jam' => $penyewaan->penyewaan_per_jam ?? [],
                            ];

                            $set('nik', $penyewaan->nik);
                            $set('tgl_booking', $penyewaan->tgl_booking);
                            $set('detail_penyewaan', json_encode($detailPenyewaan));
                            $set('total_durasi', $penyewaan->total_durasi);
                            $set('tarif', $penyewaan->tarif);
                            $set('sub_total', $penyewaan->sub_total);
                        }
                    }),

                TextInput::make('id_billing')
                    ->label('ID Billing')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->default(fn() => 'BILL-' . strtoupper(\Illuminate\Support\Str::random(8))),

                TextInput::make('nik')
                    ->label('NIK')
                    ->required()
                    ->disabled()
                    ->dehydrated(true),

                DatePicker::make('tgl_booking')
                    ->label('Tanggal Booking')
                    ->required()
                    ->disabled()
                    ->dehydrated(true),

                Textarea::make('detail_penyewaan')
                    ->label('Detail Penyewaan (JSON)')
                    ->required()
                    ->disabled()
                    ->dehydrated(true)
                    ->rows(6)
                    ->helperText('Detail penyewaan dalam format JSON'),

                TextInput::make('total_durasi')
                    ->label('Total Durasi')
                    ->required()
                    ->numeric()
                    ->disabled()
                    ->dehydrated(true),

                TextInput::make('luas')
                    ->label('Luas')
                    ->numeric()
                    ->nullable(),

                TextInput::make('tarif')
                    ->label('Tarif')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->required()
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true),

                Select::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->required()
                    ->options([
                        'ATM' => 'ATM',
                        'Mobile Banking' => 'Mobile Banking',
                        'Teller Bank' => 'Teller Bank'
                    ]),

                Select::make('status')
                    ->label('Status')
                    ->required()
                    ->default('Pending')
                    ->options([
                        'Pending' => 'Pending',
                        'Paid' => 'Paid',
                        'Failed' => 'Failed'
                    ]),

                FileUpload::make('bukti_bayar')
                    ->label('Bukti Bayar')
                    ->directory('bukti_bayar')
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'])
                    ->maxSize(5120) // 5MB
                    ->nullable()
                    ->downloadable()
                    ->previewable()
                    ->helperText('Upload bukti pembayaran (JPG, PNG, PDF - Max 5MB)'),

                DateTimePicker::make('expired_at')
                    ->label('Waktu Kadaluarsa')
                    ->nullable()
                    ->default(fn() => Carbon::now()->addHours(2))
                    ->helperText('Transaksi akan kadaluarsa setelah waktu ini'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id_billing')
                    ->label('ID Billing')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('penyewaan.user.name')
                    ->label('Nama Penyewa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nik')
                    ->label('NIK')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('penyewaan')
                    ->label('Lokasi Penyewaan')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($record) {
                        $penyewaan = Penyewaan::with(['lokasi.tempat'])
                            ->where('id_penyewaan', $record->id_penyewaan)
                            ->first();

                        if ($penyewaan && $penyewaan->lokasi && $penyewaan->lokasi->tempat) {
                            return "{$penyewaan->lokasi->tempat->nama} - {$penyewaan->lokasi->nama_lokasi}";
                        }

                        return "ID: {$record->id_penyewaan}";
                    }),

                TextColumn::make('tgl_booking')
                    ->label('Tanggal Booking')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('total_durasi')
                    ->label('Durasi')
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        $detail = json_decode($record->detail_penyewaan, true);
                        $suffix = isset($detail['tipe']) && $detail['tipe'] === 'per jam' ? ' Jam' : ' Hari';
                        return $record->total_durasi . $suffix;
                    }),

                TextColumn::make('tarif')
                    ->label('Tarif')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->money('IDR')
                    ->sortable(),

                TextColumn::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'warning',
                        'Paid' => 'success',
                        'Failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

            TextColumn::make('bukti_bayar')
                ->label('Bukti Bayar')
                ->badge()
                ->color(fn ($record) => 
                    $record->bukti_bayar && is_null($record->reviewed_at) && $record->status === 'Pending'
                        ? 'warning' 
                        : 'gray'
                )
                ->formatStateUsing(fn ($record) => 
                    $record->bukti_bayar 
                        ? (is_null($record->reviewed_at) && $record->status === 'Pending' ? 'BARU' : 'Ada') 
                        : 'Belum'
                )
                ->sortable(),

                TextColumn::make('reviewed_at')
                    ->label('Direview')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Belum direview')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('expired_at')
                    ->label('Kadaluarsa')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->color(fn ($record) => 
                        $record->expired_at && Carbon::now()->greaterThan($record->expired_at) 
                            ? 'danger' 
                            : 'gray'
                    )
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Paid' => 'Paid',
                        'Failed' => 'Failed',
                    ]),
                Tables\Filters\SelectFilter::make('metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options([
                        'ATM' => 'ATM',
                        'Mobile Banking' => 'Mobile Banking',
                        'Teller Bank' => 'Teller Bank',
                    ]),
            ])
->actions([
            Tables\Actions\ActionGroup::make([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                // Action untuk mark as reviewed
                Tables\Actions\Action::make('markReviewed')
                    ->label('Tandai Sudah Dilihat')
                    ->icon('heroicon-o-eye')
                    ->color('success')
                    ->visible(fn ($record) => 
                        $record->status === 'Pending' && 
                        $record->bukti_bayar && 
                        is_null($record->reviewed_at)
                    )
                    ->action(function ($record, $livewire) {
                        $record->update(['reviewed_at' => now()]);
                        
                        Notification::make()
                            ->title('Berhasil!')
                            ->body('Transaksi telah ditandai sudah direview.')
                            ->success()
                            ->send();

                             $livewire->resetTable();
                    }),
                    
                Tables\Actions\DeleteAction::make(),
            ])
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

public static function getNavigationBadge(): ?string
{
    $count = \App\Models\Transaksi::where('status', 'Pending')
        ->whereNotNull('bukti_bayar')
        ->whereNull('reviewed_at')
        ->count();

    return $count > 0 ? (string)$count : null;
}
}