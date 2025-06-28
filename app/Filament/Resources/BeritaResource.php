<?php

namespace App\Filament\Resources;

use App\Models\Berita;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use App\Filament\Resources\BeritaResource\Pages;

class BeritaResource extends Resource
{
    protected static ?string $model = Berita::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Dashboard Content';

    public static function getNavigationLabel(): string
    {
        return 'Berita';
    }

    public static function getPluralLabel(): string
    {
        return 'Berita';
    }

    public static function getModelLabel(): string
    {
        return 'Berita';
    }

    public static function canAccess(): bool
    {
        return Auth::check() && Auth::user()->role === 'admin';
    }

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                FileUpload::make('img')
                    ->label('Gambar Berita')
                    ->image()
                    ->directory('berita')
                    ->required(),
                Forms\Components\Textarea::make('text')
                    ->label('Teks Berita')
                    ->required(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('img')
                    ->label('Gambar')
                    ->circular()
                    ->width(50),
                TextColumn::make('text')
                    ->label('Isi')
                    ->wrap()
                    ->formatStateUsing(fn (?string $state): string => strip_tags($state ?? ''))
                    ->limit(50),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBeritas::route('/'),
            'create' => Pages\CreateBerita::route('/create'),
            'edit'   => Pages\EditBerita::route('/{record}/edit'),
        ];
    }
}

