<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Tempat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Section;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TempatResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TempatResource\RelationManagers;

class TempatResource extends Resource
{
    protected static ?string $model = Tempat::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function getNavigationLabel(): string
    {
        return 'Places'; // Ganti dengan nama yang kamu inginkan
    }
    public static function getPluralLabel(): string
    {
        return 'Places'; // Ganti dengan nama yang sesuai
    }
    public static function getModelLabel(): string
    {
        return 'Place';
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
                Section::make([
                    Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('nama')
                                ->label('Nama')
                                ->required(),
                            Forms\Components\TextInput::make('kategori')
                                ->label('Kategori')
                                ->required(),
                            Forms\Components\TextInput::make('tarif')
                                ->label('Tarif')
                                ->required(),
                        ]),

                    Forms\Components\FileUpload::make('image')
                        ->label('Image')
                        ->image() // Hanya menerima file gambar
                        ->directory('uploads/tempat') // Menyimpan ke storage
                        ->nullable(),
                ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('kategori')->label('Kategori')->sortable()->searchable(),
                Tables\Columns\ImageColumn::make('image')->label('Image')->sortable(),
                Tables\Columns\TextColumn::make('tarif')->label('Tarif')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Create At')->dateTime()->sortable(),
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
            'index' => Pages\ListTempats::route('/'),
            'create' => Pages\CreateTempat::route('/create'),
            'edit' => Pages\EditTempat::route('/{record}/edit'),
        ];
    }
}
