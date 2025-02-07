<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use Filament\Tables;
use App\Models\Lokasi;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use App\Models\Penyewaan;
use Filament\Tables\Table;
use Ramsey\Uuid\Type\Time;
use App\Rules\PenyewaanRule;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\PenyewaanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\PenyewaanResource\RelationManagers;

class PenyewaanResource extends Resource
{
    protected static ?string $model = Penyewaan::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function getNavigationLabel(): string
    {
        return 'Bookings'; // Ganti dengan nama yang kamu inginkan
    }
    public static function getPluralLabel(): string
    {
        return 'Bookings'; // Ganti dengan nama yang sesuai
    }
    public static function getModelLabel(): string
    {
        return 'Booking';
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
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('user', 'name'),

                Select::make('id_lokasi')
                    ->label('Lokasi & Tempat')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->relationship(
                        name: 'lokasi',
                        titleAttribute: 'nama_lokasi',
                        modifyQueryUsing: fn($query) => $query->with('tempat')
                    )
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->tempat->nama} - {$record->nama_lokasi}")
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if ($state) {
                            $lokasi = Lokasi::with('tempat')->where('id_lokasi', $state)->first();

                            if ($lokasi && $lokasi->tempat) {
                                if (!$get('kategori_sewa')) {
                                    $set('kategori_sewa', $lokasi->tempat->kategori_sewa);
                                }
                                $set('tarif', $lokasi->tarif);

                                // Reset total_durasi and sub_total
                                $set('total_durasi', 0);
                                $set('sub_total', 0);
                            }
                        }
                    }),

                TextInput::make('kategori_sewa')
                    ->label('Kategori Sewa')
                    ->disabled()
                    ->live()
                    ->dehydrated(true),

                DateTimePicker::make('tgl_booking')
                    ->label('Tanggal Booking')
                    ->default(now())
                    ->required(),

                Repeater::make('penyewaan_per_jam')
                    ->label('Pilih Hari dan Jam')
                    ->hidden(fn($get) => $get('kategori_sewa') !== 'per jam')
                    ->schema([
                        DatePicker::make('tgl_mulai')
                            ->label('Tanggal Pemakaian')
                            ->required()
                            ->rules(['required', 'date', 'after_or_equal:today']),
                        TimePicker::make('jam_mulai')
                            ->label('Jam Mulai')
                            ->required(),
                        TimePicker::make('jam_selesai')
                            ->label('Jam Selesai')
                            ->required()
                            ->rules(['required', 'different:jam_mulai'])
                    ])
                    // ->rules([
                    //     'array',
                    //     fn($get) => function ($attribute, $value, $fail) use ($get) {
                    //         $validator = new PenyewaanRule(
                    //             $get('id_lokasi'),
                    //             'per jam',
                    //             $get('record.id')
                    //         );

                    //         if (!$validator->passes($attribute, $value)) {
                    //             $fail($validator->message());
                    //         }
                    //     }
                    // ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->live()
                    ->afterStateUpdated(function ($set, $get) {
                        $totalJam = 0;
                        $penyewaanPerJam = $get('penyewaan_per_jam') ?? [];

                        foreach ($penyewaanPerJam as $penyewaan) {
                            if (isset($penyewaan['jam_mulai'], $penyewaan['jam_selesai'])) {
                                $baseDate = date('Y-m-d');
                                $jamMulai = Carbon::parse($baseDate . ' ' . $penyewaan['jam_mulai']);
                                $jamSelesai = Carbon::parse($baseDate . ' ' . $penyewaan['jam_selesai']);

                                if ($jamSelesai->lt($jamMulai)) {
                                    $jamSelesai->addDay();
                                }

                                // Hitung selisih dalam jam
                                $selisihJam = abs((int)$jamSelesai->diffInHours($jamMulai));
                                $totalJam += $selisihJam;;
                            }
                        }

                        $set('total_durasi', $totalJam);

                        if ($get('tarif')) {
                            $subTotal = floatval($get('tarif')) * $totalJam;
                            $set('sub_total', $subTotal);
                        }
                    })
                    ->columnSpanFull(),

                Repeater::make('penyewaan_per_hari')
                    ->label('Pilih Tanggal')
                    ->hidden(fn($get) => $get('kategori_sewa') !== 'per hari')
                    ->schema([
                        DatePicker::make('tgl_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->rules(['required', 'date', 'after_or_equal:today'])
                            ->format('d-m-Y'),
                        DatePicker::make('tgl_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->rules(['required', 'date', 'after_or_equal:tgl_mulai'])
                            ->format('d-m-Y'),
                    ])
                    // ->rules([
                    //     'array',
                    //     fn($get) => function ($attribute, $value, $fail) use ($get) {
                    //         $validator = new PenyewaanRule(
                    //             $get('id_lokasi'),
                    //             'per hari',
                    //             $get('record.id')
                    //         );

                    //         if (!$validator->passes($attribute, $value)) {
                    //             $fail($validator->message());
                    //         }
                    //     }
                    // ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->live()
                    ->afterStateUpdated(function ($set, $get) {
                        $totalHari = 0;
                        $penyewaanPerHari = $get('penyewaan_per_hari') ?? [];

                        foreach ($penyewaanPerHari as $penyewaan) {
                            if (isset($penyewaan['tgl_mulai'], $penyewaan['tgl_selesai'])) {
                                $tglMulai = Carbon::createFromFormat('Y-m-d', $penyewaan['tgl_mulai']);
                                $tglSelesai = Carbon::createFromFormat('Y-m-d', $penyewaan['tgl_selesai']);

                                // Jika tanggal sama, langsung tambahkan 1 hari
                                if ($tglMulai->isSameDay($tglSelesai)) {
                                    $selisihHari = 1;
                                } else {
                                    // Jika tanggal berbeda, hitung selisih dan tambah 1
                                    $selisihHari = $tglMulai->diffInDays($tglSelesai) + 1;
                                }

                                $totalHari += $selisihHari;
                            }
                        }

                        $set('total_durasi', $totalHari);

                        if ($get('tarif')) {
                            $subTotal = floatval($get('tarif')) * $totalHari;
                            $set('sub_total', $subTotal);
                        }
                    })
                    ->columnSpanFull(),

                TextInput::make('total_durasi')
                    ->label('Total Durasi')
                    ->numeric()
                    ->disabled()
                    ->suffix(fn($get) => $get('kategori_sewa') === 'per jam' ? 'Jam' : 'Hari')
                    ->live()
                    ->dehydrated(true),

                TextInput::make('tarif')
                    ->label('Tarif')
                    ->numeric()
                    ->prefix('Rp')
                    ->suffix(fn($get) => $get('kategori_sewa') === 'per jam' ? '/Jam' : '/Hari')
                    ->live()
                    ->disabled()
                    ->dehydrated(true)
                    ->formatStateUsing(function ($state) {
                        // Only format if state is not null
                        return !is_null($state) ? number_format((float)$state, 2, '.', ',') : null;
                    })
                    // Add this to ensure proper state handling
                    ->afterStateHydrated(function ($component, $state) {
                        if (!is_null($state)) {
                            $component->state((float)$state);
                        }
                    }),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->numeric()
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true)
                    ->live()
                    ->formatStateUsing(function ($state) {
                        // Only format if state is not null
                        return !is_null($state) ? number_format((float)$state, 2, '.', ',') : null;
                    })
                    // Add this to ensure proper state handling
                    ->afterStateHydrated(function ($component, $state) {
                        if (!is_null($state)) {
                            $component->state((float)$state);
                        }
                    }),

                TextInput::make('status')
                    ->label('Status')
                    ->default('Pending')
                    ->disabled()
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(Penyewaan::with(['lokasi.tempat']))
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


                TextColumn::make('kategori_sewa')
                    ->label('Kategori Sewa')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('penyewaan')
                    ->label('Uraian')
                    ->getStateUsing(function ($record) {
                        if (!$record) return '';

                        $schedules = null;
                        if ($record->kategori_sewa === 'per jam') {
                            $schedules = is_string($record->penyewaan_per_jam)
                                ? json_decode($record->penyewaan_per_jam, true)
                                : $record->penyewaan_per_jam;

                            if (!empty($schedules)) {
                                return implode(', ', array_map(function ($item) {
                                    return "{$item['tgl_mulai']} {$item['jam_mulai']} - {$item['jam_selesai']}";
                                }, $schedules));
                            }
                        } elseif ($record->kategori_sewa === 'per hari') {
                            $schedules = is_string($record->penyewaan_per_hari)
                                ? json_decode($record->penyewaan_per_hari, true)
                                : $record->penyewaan_per_hari;

                            if (!empty($schedules)) {
                                return implode(', ', array_map(function ($item) {
                                    return "{$item['tgl_mulai']} - {$item['tgl_selesai']}";
                                }, $schedules));
                            }
                        }

                        return '';
                    })
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_durasi')
                    ->label('Total Durasi')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('tarif')
                    ->label('Tarif')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('sub_total')
                    ->label('Sub Total')
                    ->money('IDR', true)
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori_sewa')
                    ->label('Filter Kategori Sewa')
                    ->options([
                        'per jam' => 'Per Jam',
                        'per hari' => 'Per Hari',
                    ]),
            ])
            ->defaultSort('tgl_booking', 'desc')

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
            'index' => Pages\ListPenyewaans::route('/'),
            'create' => Pages\CreatePenyewaan::route('/create'),
            'edit' => Pages\EditPenyewaan::route('/{record}/edit'),
        ];
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure kategori_sewa is preserved during creation
        if (isset($data['id_lokasi'])) {
            $lokasi = \App\Models\Lokasi::with('tempat')->find($data['id_lokasi']);
            if ($lokasi && $lokasi->tempat) {
                $data['kategori_sewa'] = $lokasi->tempat->kategori_sewa;
            }
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        // Ensure kategori_sewa is preserved during update
        if (isset($data['id_lokasi'])) {
            $lokasi = \App\Models\Lokasi::with('tempat')->find($data['id_lokasi']);
            if ($lokasi && $lokasi->tempat) {
                $data['kategori_sewa'] = $lokasi->tempat->kategori_sewa;
            }
        }
        return $data;
    }
}
