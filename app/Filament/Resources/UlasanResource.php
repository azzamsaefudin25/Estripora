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
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Rating;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
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

    // public static function canCreate(): bool
    // {
    //     return Auth::check() && Auth::user()->role === 'admin';
    // }

    // public static function canEdit(Model $record): bool
    // {
    //     return Auth::check() && Auth::user()->role === 'admin';
    // }
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
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
            ->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('penyewaan.user.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('id_penyewaan')
                    ->label('Tempat - Lokasi')
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
                    ->label('komentar')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('like')
                    ->label('Like')
                    ->searchable(),
                TextColumn::make('dislike')
                    ->label('Dislike')
                    ->searchable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->filters([])
            ->actions([
                Tables\Actions\DeleteAction::make(),
                // Tables\Actions\ActionGroup::make([
                //     // Tables\Actions\EditAction::make(),
                //     // Tables\Actions\ViewAction::make(),
                // ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
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
            // 'create' => Pages\CreateUlasan::route('/create'),
            // 'edit' => Pages\EditUlasan::route('/{record}/edit'),
        ];
    }
}
