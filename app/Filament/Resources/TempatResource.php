<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TempatResource\Pages;
use App\Filament\Resources\TempatResource\RelationManagers;
use App\Models\Tempat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class TempatResource extends Resource
{
    protected static ?string $model = Tempat::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canAccess(): bool
    {
        $canAccess = Auth::check() && Auth::user()->role === 'admin';

        if (!$canAccess) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Maaf, hanya admin yang dapat mengakses halaman ini.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function canCreate(): bool
    {
        $canCreate = Auth::check() && Auth::user()->role === 'admin';

        if (!$canCreate) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Maaf, hanya admin yang dapat membuat user baru.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function canEdit(Model $record): bool
    {
        $canEdit = Auth::check() && Auth::user()->role === 'admin';

        if (!$canEdit) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Maaf, hanya admin yang dapat mengedit user.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function canDelete(Model $record): bool
    {
        $canDelete = Auth::check() && Auth::user()->role === 'admin';

        if (!$canDelete) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Maaf, hanya admin yang dapat menghapus user.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function canView(Model $record): bool
    {
        $canView = Auth::check() && Auth::user()->role === 'admin';

        if (!$canView) {
            Notification::make()
                ->title('Akses Ditolak')
                ->body('Maaf, hanya admin yang dapat melihat detail user.')
                ->danger()
                ->send();

            return false;
        }

        return true;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required(),
                Forms\Components\TextInput::make('kategori')
                    ->required(),
                Forms\Components\FileUpload::make('image')
                    ->image() // Hanya menerima file gambar
                    ->directory('uploads/tempat') // Menyimpan ke storage
                    ->nullable(),
                Forms\Components\TextInput::make('tarif')
                    ->numeric()
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
                Tables\Actions\EditAction::make(),
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
