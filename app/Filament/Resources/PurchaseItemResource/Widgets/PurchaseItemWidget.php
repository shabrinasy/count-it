<?php

namespace App\Filament\Resources\PurchaseItemResource\Widgets;

use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;
use Filament\Facades\Filament;

class PurchaseItemWidget extends BaseWidget
{
    public $purchaseId;

    public function mount($record)
    {
        $this->purchaseId = $record;
    }

    public ?string $fromPage = null;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                \App\Models\PurchaseItem::query()->where('purchases_id', $this->purchaseId),    
            )
            ->columns([
                Tables\Columns\TextColumn::make('supplies.name')
                    ->label('Item'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->money('IDR', true)
                    ->alignEnd(),
                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR', true)
                    ->getStateUsing(function ($record) {
                        return $record->quantity * $record->price;
                    })
                    ->alignEnd()
                    ->summarize(
                        Summarizer::make()
                            ->using(function ($query) {
                                return $query->sum(DB::raw('quantity * price'));
                            })
                            ->money('IDR', true),
                    ),
            ])->actions(
                $this->fromPage === \App\Filament\Resources\PurchaseResource\Pages\ViewPurchase::class
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
