<?php

namespace App\Filament\Resources;

use Closure;
use Filament\Forms;
use App\Models\User;
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
use Illuminate\Support\Facades\Log;
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
        return 'Penyewaan';
    }
    public static function getPluralLabel(): string
    {
        return 'Penyewaan';
    }
    public static function getModelLabel(): string
    {
        return 'Penyewaan';
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
                Select::make('id_user')
                    ->label('Identitas')
                    ->required()
                    ->searchable()
                    ->disabled(fn(string $operation): bool => $operation === 'edit')
                    ->preload()
                    ->relationship('user')
                    ->getOptionLabelFromRecordUsing(fn($record) => "{$record->nik} - {$record->name}")
                    ->afterStateUpdated(function (Set $set, $state) {
                        $set('nik', null);
                        if ($state) {
                            $user = User::where('id', $state)->first();
                            if ($user) {
                                $set('nik', $user->nik);
                            }
                        }
                    }),

                TextInput::make('nik')
                    ->label('NIK')
                    ->disabled()
                    ->live()
                    ->dehydrated(true),

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

                        $set('penyewaan_per_jam', []);
                        $set('penyewaan_per_hari', []);
                        $set('total_durasi', 0);
                        $set('sub_total', 0);
                        $set('kategori_sewa', null);

                        if ($state) {
                            $lokasi = Lokasi::with('tempat')->where('id_lokasi', $state)->first();

                            if ($lokasi && $lokasi->tempat) {

                                $kategoriSewa = $lokasi->tempat->kategori_sewa;
                                $set('kategori_sewa', $kategoriSewa);
                                $set('tarif', floatval($lokasi->tarif));

                                if ($kategoriSewa === 'per jam') {

                                    $entry = [
                                        'tgl_mulai' => '',
                                        'jam_mulai' => '',
                                        'jam_selesai' => ''
                                    ];

                                    $set('penyewaan_per_jam', [$entry]);
                                } elseif ($kategoriSewa === 'per hari') {

                                    $entry = [
                                        'tgl_mulai' => '',
                                        'tgl_selesai' => ''
                                    ];

                                    $set('penyewaan_per_hari', [$entry]);
                                }
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
                    ->live()
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
                            ->rules([
                                'required',
                                'different:jam_mulai',
                                function ($get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $jamMulai = $get('jam_mulai');
                                        if (empty($jamMulai) || empty($value)) {
                                            return;
                                        }

                                        try {
                                            $baseDate = date('Y-m-d');
                                            $startTime = \Carbon\Carbon::parse($baseDate . ' ' . $jamMulai);
                                            $endTime = \Carbon\Carbon::parse($baseDate . ' ' . $value);

                                            // Handle overnight booking
                                            if ($endTime->lt($startTime)) {
                                                return;
                                            }

                                            if ($startTime->eq($endTime)) {
                                                $fail('Jam selesai tidak boleh sama dengan jam mulai.');
                                            }
                                        } catch (\Exception $e) {
                                            $fail('Format waktu tidak valid.');
                                        }
                                    };
                                }
                            ])
                    ])
                    ->rules([
                        'array',
                        function ($get, $livewire) {
                            $currentId = $livewire->record?->id_penyewaan;

                            return new PenyewaanRule(
                                $get('id_lokasi'),
                                'per jam',
                                $currentId
                            );
                        }
                    ])
                    ->minItems(1)
                    ->maxItems(10)
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

                                $selisihJam = abs((int)$jamSelesai->diffInHours($jamMulai));
                                $totalJam += $selisihJam;
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
                    ->live()
                    ->hidden(fn($get) => $get('kategori_sewa') !== 'per hari')
                    ->schema([
                        DatePicker::make('tgl_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->rules(['required', 'date', 'after_or_equal:today'])
                            ->format('Y-m-d')
                            ->displayFormat('d-m-Y'),
                        DatePicker::make('tgl_selesai')
                            ->label('Tanggal Selesai')
                            ->required()
                            ->rules([
                                'required',
                                'date',
                                'after_or_equal:tgl_mulai',
                                function ($get) {
                                    return function ($attribute, $value, $fail) use ($get) {
                                        $tglMulai = $get('tgl_mulai');
                                        if (empty($tglMulai) || empty($value)) {
                                            return;
                                        }

                                        $startDate = \Carbon\Carbon::parse($tglMulai);
                                        $endDate = \Carbon\Carbon::parse($value);

                                        if ($endDate->lt($startDate)) {
                                            $fail('Tanggal selesai tidak boleh lebih awal dari tanggal mulai.');
                                        }
                                    };
                                }
                            ])
                            ->format('Y-m-d') // Changed format to match Carbon's expected format
                            ->displayFormat('d-m-Y'), // Added display format for user-friendly view
                    ])
                    ->rules([
                        'array',
                        function ($get, $livewire) {
                            return new PenyewaanRule(
                                $get('id_lokasi'),
                                'per hari',
                                $livewire->record?->id_penyewaan
                            );
                        }
                    ])
                    ->minItems(1)
                    ->maxItems(10)
                    ->afterStateUpdated(function ($set, $get) {
                        try {
                            $totalHari = 0;
                            $penyewaanPerHari = $get('penyewaan_per_hari') ?? [];

                            foreach ($penyewaanPerHari as $penyewaan) {
                                if (
                                    isset($penyewaan['tgl_mulai'], $penyewaan['tgl_selesai']) &&
                                    !empty($penyewaan['tgl_mulai']) &&
                                    !empty($penyewaan['tgl_selesai'])
                                ) {
                                    try {
                                        $tglMulai = Carbon::parse($penyewaan['tgl_mulai']);
                                        $tglSelesai = Carbon::parse($penyewaan['tgl_selesai']);

                                        // Validate dates
                                        if (!$tglMulai || !$tglSelesai) {
                                            continue;
                                        }

                                        // Jika tanggal sama, langsung tambahkan 1 hari
                                        if ($tglMulai->isSameDay($tglSelesai)) {
                                            $selisihHari = 1;
                                        } elseif ($tglSelesai < $tglMulai) {
                                            $selisihHari = 0;
                                        } else {
                                            // Jika tanggal berbeda, hitung selisih dan tambah 1
                                            $selisihHari = $tglMulai->diffInDays($tglSelesai) + 1;
                                        }

                                        $totalHari += $selisihHari;
                                    } catch (\Exception $e) {
                                        // Skip invalid dates
                                        continue;
                                    }
                                }
                            }

                            $set('total_durasi', $totalHari);

                            if ($get('tarif')) {
                                $tarif = floatval($get('tarif'));
                                if ($tarif > 0 && $totalHari > 0) {
                                    $subTotal = $tarif * $totalHari;
                                    $set('sub_total', $subTotal);
                                }
                            }
                        } catch (\Exception $e) {
                            // Handle any unexpected errors
                            $set('total_durasi', 0);
                            $set('sub_total', 0);
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
                    ->prefix('Rp')
                    ->suffix(fn($get) => $get('kategori_sewa') === 'per jam' ? '/Jam' : '/Hari')
                    ->live()
                    ->disabled()
                    ->dehydrated(true)
                    ->formatStateUsing(function ($state) {
                        return !is_null($state) ? number_format((float)$state, 2, '.', ',') : null;
                    })
                    ->afterStateHydrated(function ($component, $state) {
                        if (!is_null($state)) {
                            $component->state((float)$state);
                        }
                    }),

                TextInput::make('sub_total')
                    ->label('Sub Total')
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true)
                    ->live()
                    ->formatStateUsing(function ($state) {
                        return !is_null($state) ? number_format((float)$state, 2, '.', ',') : null;
                    })
                    ->afterStateHydrated(function ($component, $state) {
                        if (!is_null($state)) {
                            $component->state((float)$state);
                        }
                    }),

                Select::make('status')
                    ->label('Status')
                    ->default('Pending')
                    ->options([
                        'Pending' => 'Pending',
                        'Confirmed' => 'Confirmed',
                        'Canceled' => 'Canceled'
                    ])
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

                TextColumn::make('tgl_booking')
                    ->label('Tanggal Booking')
                    ->sortable(),

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
                    ->sortable()
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
                    }),

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
            ->defaultPaginationPageOption(5)
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
