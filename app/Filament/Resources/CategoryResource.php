<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Filament\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static ?string $navigationGroup = 'Data Master';
    protected static ?string $navigationLabel = 'Kategori';
    protected static ?int $navigationSort = 5;

    protected static ?string $pluralLabel = 'Kategori'; 

    protected static ?string $title = 'Kategori'; 

    public static function getLabel(): string
    {
        return 'Kategori'; // Label ini akan digunakan untuk bagian header
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_category')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_expense')
                    ->required(),
                Forms\Components\Select::make('account_id')
                    ->label('Account')
                    ->options(function () {
                        return \App\Models\Account::where('type', 'item')
                            ->pluck('name_account', 'id');
                    })
                    ->searchable()
                    ->preload()
                    ->required(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name_category')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_expense')
                    ->label('Expense')
                    ->boolean(),
                Tables\Columns\TextColumn::make('account.name_account')
                    ->label('Account')
                    ->formatStateUsing(function ($state, $record) {
                        return $record->account?->code_account . ' - ' . $record->account?->name_account;
                    }),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }

}
