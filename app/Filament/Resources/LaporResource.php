<?php

namespace App\Filament\Resources;

use App\Models\Lapors;
use App\Models\User;
use App\Models\Penyewaan;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;         
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Filament\Resources\LaporResource\Pages;

class LaporResource extends Resource
{
    protected static ?string $model = Lapors::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationGroup = 'Feedback & Laporan';

    public static function getNavigationLabel(): string
    {
        return 'Lapor';
    }
    public static function getPluralLabel(): string
    {
        return 'Laporan';
    }
    public static function getModelLabel(): string
    {
        return 'Laporan';
    }

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }
    public static function canCreate(): bool
    {
        return Auth::user()->role === 'admin';
    }
    public static function canEdit(Model $record): bool
    {
        return Auth::user()->role === 'admin';
    }
    public static function canDelete(Model $record): bool
    {
        return Auth::user()->role === 'admin';
    }
    public static function canView(Model $record): bool
    {
        return Auth::user()->role === 'admin';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Detail Laporan')
                    ->schema([
                       Select::make('id_penyewaan')
                            ->label('Penyewaan')
                            ->options(function (?Lapors $record) {
                                if (! $record) {
                                    return [];
                                }
                                $userId = User::where('email', $record->email)->value('id');

                                return Penyewaan::with('lokasi.tempat')
                                    ->where('status', 'Confirmed')
                                    ->where('id_user', $userId)
                                    ->get()
                                    ->mapWithKeys(function ($p) {
                                        $tglBooking = Carbon::parse($p->tgl_booking)->format('d M Y');
                                        
                                        $rincian = $p->kategori_sewa === 'per jam'
                                            ? collect($p->penyewaan_per_jam)
                                                ->map(fn($j) => "{$j['tgl_mulai']} {$j['jam_mulai']}-{$j['jam_selesai']}")
                                                ->join('; ')
                                            : collect($p->penyewaan_per_hari)
                                                ->map(fn($h) => "{$h['tgl_mulai']}" . ($h['tgl_mulai'] != $h['tgl_selesai'] ? " s/d {$h['tgl_selesai']}" : ''))
                                                ->join('; ');
                                        return [
                                            $p->id_penyewaan => "{$p->lokasi->tempat->nama} — {$tglBooking} | {$rincian}"
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->required()
                            ->searchable(),
                        Textarea::make('keluhan')
                            ->label('Keluhan')
                            ->required()
                            ->rows(3),
                        FileUpload::make('foto')
                            ->label('Foto 1')
                            ->image()
                            ->directory('lapor_foto'),
                        FileUpload::make('foto2')
                            ->label('Foto 2')
                            ->image()
                            ->directory('lapor_foto'),
                        FileUpload::make('foto3')
                            ->label('Foto 3')
                            ->image()
                            ->directory('lapor_foto'),
                        Textarea::make('balasan')
                            ->label('Balasan (admin)')
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pelapor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('penyewaan.lokasi.tempat.nama')
                    ->label('Tempat')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('penyewaan.lokasi.nama_lokasi')
                    ->label('Lokasi')
                    ->sortable(),
                TextColumn::make('penyewaan.tgl_booking')
                    ->label('Tanggal Pesan')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('penyewaan.kategori_sewa')
                    ->label('Kategori'),
                TextColumn::make('penyewaan.total_durasi')
                    ->label('Durasi')
                    ->suffix(fn($state, $record) => $record->penyewaan->kategori_sewa === 'per jam' ? ' jam' : ' hari'),
                TextColumn::make('keluhan')
                    ->label('Keluhan')
                    ->wrap()
                    ->limit(50),
                TextColumn::make('balasan')
                    ->label('Balasan')
                    ->wrap()
                    ->limit(50),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
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
            'index'   => Pages\ListLapor::route('/'),
            'create'  => Pages\CreateLapor::route('/create'),
            'edit'    => Pages\EditLapor::route('/{record}/edit'),
        ];
    }
}
