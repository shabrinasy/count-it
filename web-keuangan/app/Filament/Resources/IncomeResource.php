<?php

namespace App\Filament\Resources;

use App\Filament\Resources\IncomeResource\Pages;
use App\Filament\Resources\IncomeResource\RelationManagers;
use App\Models\Income;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class IncomeResource extends Resource
{
    protected static ?string $model = Income::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Pemasukan';

    protected static ?string $pluralLabel = 'Pemasukan'; 

    protected static ?string $title = 'Pemasukan'; 

    public static function getLabel(): string
    {
        return 'Pemasukan'; // Label ini akan digunakan untuk bagian header
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code_income')
                    ->label('Code')
                    ->default(Income::getKode())  // Set default value here
                    ->disabled(),
                Forms\Components\DatePicker::make('date_income')
                    ->required()
                    ->label('Transaction Date')
                    ->maxDate(now()),
                Forms\Components\TextInput::make('name_income')
                    ->required()
                    ->label('Name'),
                Forms\Components\TextInput::make('amount_income')
                    ->required()
                    ->prefix('IDR')
                    ->label('Amount'),
                Forms\Components\Select::make('category_id')
                    ->required()
                    ->label('Category')
                    ->relationship('category', 'name_category', function ($query) {
                        $query->where('is_expense', false); 
                    }),
                Forms\Components\TextInput::make('note_income')
                    ->maxLength(255)
                    ->default(null)
                    ->label('Note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code_income')
                    ->searchable()
                    ->sortable()
                    ->label('Code'),
                Tables\Columns\TextColumn::make('date_income')
                    ->date('d F y')
                    ->sortable()
                    ->label('Transaction Date'),
                Tables\Columns\TextColumn::make('name_income')
                    ->searchable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('amount_income')
                    ->money('IDR', true)
                    ->sortable()
                    ->label('Amount'),
                Tables\Columns\TextColumn::make('category.name_category')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('note_income')
                    ->searchable()
                    ->label('Note'),
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
            'index' => Pages\ListIncomes::route('/'),
            'create' => Pages\CreateIncome::route('/create'),
            'edit' => Pages\EditIncome::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }

}
