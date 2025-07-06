<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseItemResource\Pages;
use App\Filament\Resources\PurchaseItemResource\RelationManagers;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplies;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Grid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;


class PurchaseItemResource extends Resource
{
    protected static ?string $model = PurchaseItem::class;

    protected static bool $shouldRegisterNavigation = false;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $purchase = new Purchase();
        if (request()->filled('purchase_id')) {
            $purchase = Purchase::find(request('purchase_id'));
        }

        return $form
            ->schema([
                Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('purchases_code')
                    ->label('Code')
                    ->required()
                    ->disabled()
                    ->default($purchase->code),
                    Forms\Components\DatePicker::make('date')
                        ->label('Date')
                        ->disabled()
                        ->default($purchase->date)
                        ->required(),
                    Forms\Components\TextInput::make('suppliers_id')
                        ->label('Supplier')
                        ->default($purchase->supplier?->name)
                        ->disabled()
                        ->required(),
                ])
                ->columns(3),
                Grid::make()
                    ->schema([
                        Forms\Components\Select::make('supplies_id')
                            ->label('Item')
                            ->required()
                            ->options(
                                \App\Models\Supplies::all()->pluck('name', 'id'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $supply = \App\Models\Supplies::find($state);
                                $set('unit', $supply->unit ?? '-');
                            }),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->required()
                            ->prefix('IDR')
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $qty = $get('quantity') ?? 0;
                                $set('total', $qty * $state);
                            }),
                        Forms\Components\TextInput::make('total')
                            ->label('Total')
                            ->prefix('IDR')
                            ->readOnly()
                            ->dehydrated(false) // penting agar tidak dikirim ke database
                            ->reactive(),
                        Forms\Components\Hidden::make('purchases_id')
                            ->default(request('purchase_id')),
                        ])
                    ->columns(3),
                Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('quantity')
                            ->label(new HtmlString('Qty <abbr title="Jumlah unit barang yang dibeli dalam transaksi ini" style="text-decoration: none; cursor: help; color: #60a5fa;">&#9432;</abbr>'))
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $price = $get('price') ?? 0;
                                $set('total', $state * $price);
                            }),
                        Forms\Components\Select::make('unit_purchase')
                            ->label(new HtmlString('Unit Purchase <abbr title="Unit barang yang dibeli (misal: pack, box, pcs)" style="text-decoration: none; cursor: help; color: #60a5fa;">&#9432;</abbr>'))
                            ->required()
                            ->options([
                                'pcs' => 'pcs',
                                'pack' => 'pack',
                                'box' => 'box',
                                'liter' => 'L',
                                'kg' => 'kg',
                                'gram' => 'gram',
                                'ml' => 'ml',
                            ]),
                        Forms\Components\TextInput::make('actual_weight')
                            ->label(new HtmlString('Berat per unit (stok) <abbr title="Berat dari 1 unit pembelian. Misal: 1 pack = 250 gram" style="text-decoration: none; cursor: help; color: #60a5fa;">&#9432;</abbr>'))
                            ->required(),
                        Forms\Components\TextInput::make('unit')
                            ->label('Unit (Stok)')
                            ->disabled(),
                    ])
                    ->columns(4),
            ]);
    }

    // public static function table(Table $table): Table
    // {
    //     return $table
    //         ->columns([
    //             Tables\Columns\TextColumn::make('purchase.code')
    //                 ->numeric()
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('supplies.name')
    //                 ->numeric()
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('quantity')
    //                 ->numeric()
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('price')
    //                 ->money('IDR', true)
    //                 ->sortable(),
    //             Tables\Columns\TextColumn::make('created_at')
    //                 ->dateTime()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //             Tables\Columns\TextColumn::make('updated_at')
    //                 ->dateTime()
    //                 ->sortable()
    //                 ->toggleable(isToggledHiddenByDefault: true),
    //         ])
    //         ->filters([
    //             //
    //         ])
    //         ->actions([
    //             Tables\Actions\EditAction::make(),
    //         ])
    //         ->bulkActions([
    //             Tables\Actions\BulkActionGroup::make([
    //                 Tables\Actions\DeleteBulkAction::make(),
    //             ]),
    //         ]);
    // }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseItems::route('/'),
            'create' => Pages\CreatePurchaseItem::route('/create'),
            'edit' => Pages\EditPurchaseItem::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'pembelian']);
    }

}
