<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Lokasi;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LokasiResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\LokasiResource\RelationManagers;

class LokasiResource extends Resource
{
    protected static ?string $model = Lokasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    public static function getNavigationLabel(): string
    {
        return 'Locations'; // Ganti dengan nama yang kamu inginkan
    }
    public static function getPluralLabel(): string
    {
        return 'Locations'; // Ganti dengan nama yang sesuai
    }
    public static function getModelLabel(): string
    {
        return 'Location';
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
                Select::make('id_tempat')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('tempat', 'nama'),
                TextInput::make('nama_lokasi')
                    ->label('Nama Lokasi')
                    ->required(),
                TextInput::make('tarif')
                    ->label('Tarif')
                    ->numeric()
                    ->prefix('Rp')
                    ->formatStateUsing(fn($state) => number_format($state, 0, ',', '.'))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tempat.nama')->label('Nama Tempat')->sortable()->searchable(),
                TextColumn::make('nama_lokasi')->label('Nama Lokasi')->sortable()->searchable(),
                TextColumn::make('tarif')
                    ->label('Tarif')
                    ->formatStateUsing(fn(string $state): string => 'Rp ' . number_format($state, 2, ',', '.'))
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
            'index' => Pages\ListLokasis::route('/'),
            'create' => Pages\CreateLokasi::route('/create'),
            'edit' => Pages\EditLokasi::route('/{record}/edit'),
        ];
    }
}
