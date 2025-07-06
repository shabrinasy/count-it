<?php

namespace App\Filament\Resources;

use Closure;
use App\Filament\Resources\OrderItemResource\Pages;
use App\Filament\Resources\OrderItemResource\RelationManagers;
use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Menu;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\FormsComponent;
use Filament\Forms\Components\Hidden;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



class OrderItemResource extends Resource
{
    protected static ?string $model = OrderItem::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $order = new Order();
        if (request()->filled('order_id')) {
            $order = Order::find(request('order_id'));
        }

        return $form
            ->schema([
                Grid::make()
                ->schema([
                    Forms\Components\TextInput::make('orders_code')
                        ->label('Code')
                        ->required()
                        ->disabled()
                        ->default($order->code),
                ])
                ->columns(3),
                Grid::make()
                    ->schema([
                        Forms\Components\Select::make('menus_id')
                            ->label('Menu')
                            ->required()
                            ->options(
                                \App\Models\Menu::all()->pluck('name', 'id'))
                            ->rules([
                                function (callable $get): \Closure {
                                    return function (string $attribute, $value, \Closure $fail) {
                                        $menuHasBom = \App\Models\Menu::where('id', $value)->whereHas('billOfMaterial')->exists();
                                        if (!$menuHasBom) {
                                            $menu = \App\Models\Menu::find($value);
                                            $fail("Menu \"{$menu?->name}\" belum memiliki BOM.");
                                        }
                                    };
                                }
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $menu = \App\Models\Menu::find($state);
                                $set('price', $menu->price ?? '-');
                            }),
                        Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->numeric()
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $price = $get('price') ?? 0;
                                $set('total', $state * $price);
                            }),
                        Forms\Components\TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->readOnly()
                            ->default(function (Get $get) {
                                $menu = \App\Models\Menu::find($get('menus_id'));
                                return $menu?->price ?? 0;
                            })
                            ->prefix('IDR'),
                        Forms\Components\TextInput::make('total')
                            ->label('Total'),
                        Forms\Components\Hidden::make('orders_id')
                            ->default(request('order_id')),
                        ])
                    ->columns(4)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('orders_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('menus_id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
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
            'index' => Pages\ListOrderItems::route('/'),
            'create' => Pages\CreateOrderItem::route('/create'),
            'edit' => Pages\EditOrderItem::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'penjualan']);
    }

}
