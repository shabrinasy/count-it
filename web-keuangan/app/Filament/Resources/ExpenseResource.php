<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-circle';
    protected static ?string $navigationLabel = 'Pengeluaran';

    protected static ?string $pluralLabel = 'Pengeluaran'; 

    protected static ?string $title = 'Pengeluaran'; 

    public static function getLabel(): string
    {
        return 'Pengeluaran'; // Label ini akan digunakan untuk bagian header
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code_expense')
                    ->label('Code')
                    ->default(Expense::getKode())  // Set default value here
                    ->disabled(),
                Forms\Components\DatePicker::make('date_expense')
                    ->required()
                    ->label('Transaction Date')
                    ->maxDate(now()),
                Forms\Components\TextInput::make('name_expense')
                    ->required()
                    ->label('Name'),
                Forms\Components\TextInput::make('amount_expense')
                    ->required()
                    ->prefix('IDR')
                    ->label('Amount'),
                Forms\Components\Select::make('category_id')
                    ->required()
                    ->label('Category')
                    ->relationship('category', 'name_category', function ($query) {
                        $query->where('is_expense', true); 
                    }),
                Forms\Components\TextInput::make('note_expense')
                    ->maxLength(255)
                    ->default(null)
                    ->label('Note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code_expense')
                    ->searchable()
                    ->sortable()
                    ->label('Code'),
                Tables\Columns\TextColumn::make('date_expense')
                    ->date('d F y')
                    ->sortable()
                    ->label('Transaction Date'),
                Tables\Columns\TextColumn::make('name_expense')
                    ->searchable()
                    ->label('Name'),
                Tables\Columns\TextColumn::make('amount_expense')
                    ->money('IDR', true)
                    ->sortable()
                    ->label('Amount'),
                Tables\Columns\TextColumn::make('category.name_category')
                    ->label('Category'),
                Tables\Columns\TextColumn::make('note_expense')
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
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }

}
