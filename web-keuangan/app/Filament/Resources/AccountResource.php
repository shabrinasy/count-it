<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccountResource\Pages;
use App\Filament\Resources\AccountResource\RelationManagers;
use App\Models\Account;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;


class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Akun';

    protected static ?string $pluralLabel = 'Akun'; 
    
    protected static ?string $title = 'Akun'; 
    
    public static function getLabel(): string
    {
        return 'Akun'; // Label ini akan digunakan untuk bagian header
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        return $query->orderByRaw("
            CAST(SUBSTRING(code_account, 1, 1) AS UNSIGNED) ASC,
            CAST(SUBSTRING(code_account, 2, 1) AS UNSIGNED) ASC,
            CAST(SUBSTRING(code_account, 3, 2) AS UNSIGNED) ASC
        ");
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code_account')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Code'),
                Forms\Components\TextInput::make('name_account')
                    ->required()
                    ->label('Name')
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('balance')
                ->required()
                ->options([
                    'debit' => 'Debit',
                    'credit' => 'Credit',
                ]),
                Forms\Components\Select::make('type')
                ->required()
                ->options([
                    'header' => 'Header',
                    'subheader' => 'Subheader',
                    'item' => 'Item',
                ])
                ->live(),
                Forms\Components\Select::make('account_activity')
                ->options([
                    'operating' => 'Operating Activity',
                    'investing' => 'Investing Activity',
                    'financing' => 'Financing Activity',
                ])
                ->nullable(),
                Forms\Components\Select::make('parent')
                ->label('Parent Account')
                ->options(function (callable $get) {
                    $type = $get('type');

                    if ($type === 'subheader') {
                        return \App\Models\Account::where('type', 'header')
                            ->pluck('name_account', 'id');
                    }

                    if ($type === 'item') {
                        return \App\Models\Account::where('type', 'subheader')
                            ->pluck('name_account', 'id');
                    }

                    return [];
                })
                ->disabled(fn (callable $get) => $get('type') === 'header')
                ->searchable()
                ->preload()
                ->reactive(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code_account')
                    ->label('Code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_account')
                    ->label('Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('balance')
                    ->label('Balance')
                    ->formatStateUsing(fn (string $state): string => Str::ucfirst($state)),
                Tables\Columns\TextColumn::make('type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => Str::ucfirst($state)),
                Tables\Columns\TextColumn::make('account_activity')
                    ->label('Activity')
                    ->default('-')
                    ->formatStateUsing(fn (string $state): string => Str::ucfirst($state)),
                // Tables\Columns\TextColumn::make('parent')
                //     ->searchable(),
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
            'index' => Pages\ListAccounts::route('/'),
            'create' => Pages\CreateAccount::route('/create'),
            'edit' => Pages\EditAccount::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'keuangan']);
    }

}
