<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LokasiResource\Pages;
use App\Filament\Resources\LokasiResource\RelationManagers;
use App\Models\Lokasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

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
                Forms\Components\Select::make('id_tempat')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('tempat', 'nama'),
                Forms\Components\TextInput::make('nama_lokasi')
                    ->label('Nama Lokasi')
                    ->required(),
                Forms\Components\TextInput::make('tarif')
                    ->label('Tarif')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
