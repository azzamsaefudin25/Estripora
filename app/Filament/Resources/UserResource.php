<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Gate;
use Filament\Forms\FormsComponent;
use Filament\Resources\Pages\Page;
use Forms\Component\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\UserResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\UserResource\RelationManagers;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

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
                Forms\Components\TextInput::make('nik')
                    ->label('NIK')
                    ->unique(ignoreRecord: true)
                    ->required(),

                Forms\Components\TextInput::make('name')
                    ->label('Nama')
                    ->required(),

                Forms\Components\TextInput::make('phone')
                    ->label('No HP')
                    ->tel(),

                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->required(),

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->default(now()),

                Forms\Components\TextInput::make('username')
                    ->label('Username')
                    ->unique(ignoreRecord: true)
                    ->required(),

                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(Page $livewire): bool =>  $livewire instanceof CreateRecord),

                Forms\Components\TextInput::make('role')
                    ->label('Role')
                    ->default('user')
                    ->required(),

            ]); // Hanya admin yang bisa mengakses form;

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nik')->label('NIK')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Nama')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('No HP')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email')->label('Email')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')->label('Email Verified At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('username')->label('Username')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('created_at')->label('Create At')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('role')->label('Role')->sortable()->searchable(),

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
            ]); // Hanya admin yang bisa mengakses form;
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
