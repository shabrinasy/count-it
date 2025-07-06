<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuppliesResource\Pages;
use App\Filament\Resources\SuppliesResource\RelationManagers;
use App\Models\Supplies;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuppliesResource extends Resource
{
    protected static ?string $model = Supplies::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationLabel = 'Bahan Baku';

    protected static ?string $pluralLabel = 'Bahan Baku'; 

    protected static ?string $title = 'Bahan Baku'; 

    public static function getLabel(): string
    {
        return 'Bahan Baku'; // Label ini akan digunakan untuk bagian header
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Forms\Components\Select::make('category_supplies_id')
                    ->label('Category')
                    ->options(function () {
                        return \App\Models\CategorySupplies::all()->pluck('name', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->createOptionForm(
                        \App\Filament\Resources\CategorySuppliesResource::getForm()
                    ),
                Forms\Components\TextInput::make('stock')
                    ->label('Initial Stock')
                    ->disabledOn('edit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('unit')
                    ->required()
                    ->disabledOn('edit')
                    ->options([
                        'pcs' => 'pcs',
                        'pack' => 'pack',
                        'box' => 'box',
                        'liter' => 'L',
                        'kg' => 'kg',
                        'gram' => 'gram',
                        'ml' => 'ml',
                    ])
                    ->searchable(),
                    ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('categorySupplies.name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->searchable(),
                Tables\Columns\TextColumn::make('unit')
                    ->searchable(),
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
            'index' => Pages\ListSupplies::route('/'),
            'create' => Pages\CreateSupplies::route('/create'),
            'edit' => Pages\EditSupplies::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'pembelian']);
    }

}
