<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryMenuResource\Pages;
use App\Filament\Resources\CategoryMenuResource\RelationManagers;
use App\Models\CategoryMenu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class CategoryMenuResource extends Resource
{
    protected static ?string $model = CategoryMenu::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationParentItem = 'Menus';
    protected static ?string $navigationLabel = 'Kategori Menu';
        protected static bool $shouldRegisterNavigation = false;

    public static function getForm(){
        return [
            Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image(),
        ];
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                self::getForm()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListCategoryMenus::route('/'),
            'create' => Pages\CreateCategoryMenu::route('/create'),
            'edit' => Pages\EditCategoryMenu::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'penjualan']);
    }

}
