<?php

namespace App\Filament\Resources\OrderItemResource\Widgets;

use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Widgets\TableWidget as BaseWidget;

class OrderItemWidget extends BaseWidget
{
    public $orderId;

    public function mount($record)
    {
        $this->orderId = $record;
    }

    public ?string $fromPage = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\OrderItem::query()
                    ->where('orders_id', $this->orderId),
            )
            ->columns([
                Tables\Columns\TextColumn::make('menu.name')
                    ->label('Name'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->alignEnd()
                    ->money('IDR', true),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->getStateUsing(function ($record){
                        return $record->quantity * $record->price;
                    })
                    ->alignEnd()
                    ->money('IDR', true)
                    ->summarize(
                        Summarizer::make()
                        ->using(function ($query){
                            return $query->sum(DB::raw('quantity * price'));
                        })
                        ->money('IDR', true),
                    ),
            ])->actions(
                $this->fromPage === \App\Filament\Resources\OrderResource\Pages\ViewOrder::class
                    ? []
                    : [
                        Tables\Actions\EditAction::make()
                            ->form([
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required(),
                            ]),
                        Tables\Actions\DeleteAction::make(),
            ]);
    }
}
