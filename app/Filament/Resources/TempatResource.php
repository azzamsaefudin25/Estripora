<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Tempat;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;
use Filament\Resources\Resource;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\TempatResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TempatResource\RelationManagers;

class TempatResource extends Resource
{
    protected static ?string $model = Tempat::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    public static function getNavigationLabel(): string
    {
        return 'Tempat';
    }
    public static function getPluralLabel(): string
    {
        return 'Tempat';
    }
    public static function getModelLabel(): string
    {
        return 'Tempat';
    }
    public static function getnavigationGroup(): ?string
    {
        return 'Kelola Tempat & Area';
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
                            TextInput::make('nama')
                                ->label('Nama')
                                ->required()->rules([
                                    function (callable $get) {
                                        return Rule::unique('tempat', 'nama')
                                            ->ignore($get('id'), 'id');
                                    },
                                ])
                                ->validationMessages([
                                    'unique' => 'Tempat yang sama sudah dibuat.',
                                ]),
                            TextInput::make('kategori')
                                ->label('Kategori')
                                ->required(),
                            TextInput::make('rentang_harga')
                                ->label('Rentang Harga')
                                ->required(),
                            MarkdownEditor::make('deskripsi')
                                ->label('Deskripsi')
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strike',
                                    'bulletList',
                                    'orderedList',
                                    'link',
                                    'blockquote',
                                    'codeBlock',
                                ])
                                ->placeholder('Masukkan deskripsi...')
                                ->columnSpanFull(),
                            Select::make('kategori_sewa')
                                ->label('Kategori Sewa')
                                ->options([
                                    'per jam' => 'per jam',
                                    'per hari' => 'per hari',
                                ]),
                        ]),
                    FileUpload::make('image')
                        ->label('Image')
                        ->image()
                        ->directory('uploads/tempat')
                        ->nullable(),
                    FileUpload::make('image2')
                        ->label('Image 2')
                        ->image()
                        ->directory('uploads/tempat')
                        ->nullable(),
                    FileUpload::make('image3')
                        ->label('Image 3')
                        ->image()
                        ->directory('uploads/tempat')
                        ->nullable(),
                    FileUpload::make('image4')
                        ->label('Image 4')
                        ->image()
                        ->directory('uploads/tempat')
                        ->nullable(),
                    FileUpload::make('image5')
                        ->label('Image 5')
                        ->image()
                        ->directory('uploads/tempat')
                        ->nullable(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->label('Nama')->sortable()->searchable(),
                TextColumn::make('kategori')->label('Kategori')->sortable()->searchable(),
                TextColumn::make('deskripsi')->label('Deskripsi')->sortable()->searchable(),
                TextColumn::make('kategori_sewa')->label('Kategori Sewa')->sortable()->searchable(),
                TextColumn::make('rentang_harga')->label('Rentang Harga')->sortable()->searchable(),
                ImageColumn::make('image')->label('Image')->sortable(),
                TextColumn::make('created_at')->label('Create At')->dateTime()->sortable(),
            ])
            ->defaultPaginationPageOption(5)
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('kategori_sewa')
                    ->label('Kategori Sewa')
                    ->options([
                        'per jam' => 'Per Jam',
                        'per hari' => 'Per Hari',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                // Tables\Actions\BulkActionGroup::make([]),
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
