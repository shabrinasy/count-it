<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\OrderItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?string $pluralLabel = 'Penjualan'; 

    protected static ?string $title = 'Penjualan'; 

    public static function getLabel(): string
    {
        return 'Penjualan'; // Label ini akan digunakan untuk bagian header
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->default(Order::getKodeOrder())  // Set default value here
                    ->disabled(),
                Forms\Components\Select::make('payment')
                    ->required()
                    ->options([
                        'qris' => 'QRIS',
                        'cash' => 'Cash',
                    ])
                    ->searchable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d F Y')
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('orderItems_sum_quantity')
                    ->label('Total Item')
                    ->state(function (Order $record) {
                        return optional($record->orderItem)->sum('quantity') ?? 0;
                    })
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_payment')
                    ->label('Total Payment')
                    ->state(function (Order $record) {
                        $total = optional($record->orderItem)->sum(fn ($item) => $item->quantity * $item->price) ?? 0;
                        return 'IDR ' . number_format($total, 0, ',', '.');
                    })
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('payment')
                    ->searchable()
                    ->formatStateUsing(fn ($state) => [
                        'qris' => 'QRIS',
                        'cash' => 'Cash',
                    ][$state] ?? '-'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.orders.detail', ['record' => $record->id]))
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'detail' => Pages\ViewOrder::route('/{record}/detail'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'penjualan']);
    }

}
