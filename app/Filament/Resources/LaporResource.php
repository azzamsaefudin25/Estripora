<?php

namespace App\Filament\Resources;

use App\Models\Lapors;
use App\Models\User;
use App\Models\Penyewaan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Filament\Resources\LaporResource\Pages;
use Filament\Forms\Components\Placeholder;

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
        return false; // Admin tidak bisa create
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
                        // Nama Pelapor
                        Placeholder::make('pelapor')
                            ->label('Pelapor')
                            ->content(fn (?Lapors $record): string => $record?->user?->name ?? '-'),
                        // Email Pelapor
                        Placeholder::make('email')
                            ->label('Email Pelapor')
                            ->content(fn (?Lapors $record): string => $record?->email ?? '-'),

                        // Nama Tempat
                        Placeholder::make('tempat')
                            ->label('Tempat')
                            ->content(fn (?Lapors $record): string => $record?->penyewaan?->lokasi?->tempat?->nama ?? '-'),

                        // Nama Lokasi
                        Placeholder::make('nama_lokasi')
                            ->label('Nama Lokasi')
                            ->content(fn (?Lapors $record): string => $record?->penyewaan?->lokasi?->nama_lokasi ?? '-'),

                        // Tanggal Booking
                        Placeholder::make('tgl_booking')
                            ->label('Tanggal Pesan')
                            ->content(fn (?Lapors $record): string => $record 
                                ? Carbon::parse($record->penyewaan->tgl_booking)->format('d M Y') 
                                : '-'
                            ),

                        // Kategori
                        Placeholder::make('kategori_sewa')
                            ->label('Kategori')
                            ->content(fn (?Lapors $record): string => ucfirst($record?->penyewaan?->kategori_sewa ?? '-')),

                        // Durasi
                        Placeholder::make('durasi')
                            ->label('Durasi')
                            ->content(fn (?Lapors $record): string => $record
                                ? $record->penyewaan->total_durasi 
                                    . ' ' 
                                    . ($record->penyewaan->kategori_sewa === 'per jam' ? 'jam' : 'hari')
                                : '-'
                            ),

                        // Keluhan — read-only textarea
                        Textarea::make('keluhan')
                            ->label('Keluhan')
                            ->disabled()
                            ->rows(3),

                        // Foto-foto — hanya tampilkan
                        FileUpload::make('foto')
                            ->label('Foto 1')
                            ->disk('public')  
                            ->directory('lapor_foto')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120) 
                            ->downloadable()     
                            ->previewable(),
                            

                        FileUpload::make('foto2')
                            ->label('Foto 2')
                            ->disk('public')  
                            ->directory('lapor_foto')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->previewable(),
                            

                        FileUpload::make('foto3')
                            ->label('Foto 3')
                            ->disk('public')  
                            ->directory('lapor_foto')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                            ->maxSize(5120)
                            ->downloadable()
                            ->previewable(),
                            

                        // Hanya balasan yang bisa diisi/diubah
                        Textarea::make('balasan')
                            ->label('Balasan (admin)')
                            ->rows(4)
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Pelapor')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
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
                    ->limit(50)
                    ->formatStateUsing(fn (?string $state): string => strip_tags($state ?? '')),
                TextColumn::make('balasan')
                    ->label('Balasan')
                    ->wrap()
                    ->limit(50)
                    ->formatStateUsing(fn (?string $state): string => strip_tags($state ?? '')),
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

    public static function getNavigationBadge(): ?string
    {
        // Hitung laporan yang belum punya balasan
        $count = Lapors::query()
            ->whereNull('balasan')
            ->orWhere('balasan', '')
            ->count();

        return $count ? (string) $count : null;
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLapor::route('/'),
            'edit'  => Pages\EditLapor::route('/{record}/edit'),
            
        ];
    }
}
