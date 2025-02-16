<?php

namespace App\Filament\Resources;

use Carbon\Carbon;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Ulasan;
use Filament\Forms\Get;
use Filament\Forms\Form;
use App\Models\Penyewaan;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Rating;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\UlasanResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UlasanResource\RelationManagers;

class UlasanResource extends Resource
{
    protected static ?string $model = Ulasan::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    public static function getNavigationLabel(): string
    {
        return 'Ulasan'; 
    }
    public static function getPluralLabel(): string
    {
        return 'Ulasan'; 
    }
    public static function getModelLabel(): string
    {
        return 'Ulasan';
    }

    public static function getnavigationGroup(): ?string
    {
        return 'Feedback & Laporan';
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
                        return User::whereHas('penyewaan', function ($query) {
                            $query->where('status', 'confirmed');
                        })
                            ->pluck('name', 'nik')
                            ->mapWithKeys(function ($name, $nik) {
                                return [$nik => "{$nik} - {$name}"];
                            })
                            ->toArray();
                    })
                    ->live()
                    ->afterStateUpdated(function ($set, $state) {
                        if (!$state) {
                            $set('id_penyewaan', null);
                            $set('ulasan', null);
                            $set('rating', null);
                            return;
                        }

                        $penyewaanList = Penyewaan::with(['lokasi.tempat'])
                            ->where('nik', $state)
                            ->where('status', 'confirmed')
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

                Select::make('id_penyewaan')
                    ->label('Penyewaan')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(fn(Get $get): array => $get('penyewaan_options') ?? [])
                    ->live()
                    ->afterStateUpdated(function ($set, $get, $state) {
                        if (!$state) {
                            $set('ulasan', null);
                            $set('rating', null);
                            return;
                        }
                    }),

                Select::make('rating')
                    ->label('Rating')
                    ->required()
                    ->options([
                        1 => '⭐ (1)',
                        2 => '⭐⭐ (2)',
                        3 => '⭐⭐⭐ (3)',
                        4 => '⭐⭐⭐⭐ (4)',
                        5 => '⭐⭐⭐⭐⭐ (5)',
                    ]),

                Textarea::make('ulasan')
                    ->label('Ulasan')
                    ->required()
                    ->rows(3)
                    ->maxLength(255),
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
                TextColumn::make('rating')
                    ->label('Rating')
                    ->formatStateUsing(fn(int $state): string => str_repeat('⭐', $state))
                    ->sortable(),
                TextColumn::make('ulasan')
                    ->label('Ulasan')
                    ->limit(50)
                    ->searchable(),
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
            'index' => Pages\ListUlasans::route('/'),
            'create' => Pages\CreateUlasan::route('/create'),
            'edit' => Pages\EditUlasan::route('/{record}/edit'),
        ];
    }
}
