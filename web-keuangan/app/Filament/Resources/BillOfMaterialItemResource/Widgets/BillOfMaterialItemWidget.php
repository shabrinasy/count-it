<?php

namespace App\Filament\Resources\BillOfMaterialItemResource\Widgets;

use App\Models\BillOfMaterialItem;
use App\Models\Supplies;
use App\Models\CategorySupplies;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Filament\Forms\Set;

class BillOfMaterialItemWidget extends TableWidget
{
    protected static ?string $heading = 'Bill of Material';

    public int|string|null $bom_id = null;
    public int|string|null $menus_id = null;

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                fn () => BillOfMaterialItem::query()
                    ->where('bill_of_materials_id', $this->bom_id)
            )
            ->columns([
                TextColumn::make('supplies.name')->label('Item'),
                TextColumn::make('quantity')->label('Qty'),
                TextColumn::make('unit')->label('Unit'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('addItem')
                    ->label('Add Material')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('supplies_id')
                            ->label('Item')
                            ->options(Supplies::all()->pluck('name', 'id'))
                            ->reactive()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $supply = Supplies::find($state);
                                $set('unit', $supply?->unit ?? '');
                                $set('category_name', $supply?->categorySupplies?->name ?? '-');
                            })
                            ->required(),

                        TextInput::make('category_name')
                            ->label('Category')
                            ->readOnly()
                            ->required(),

                        TextInput::make('quantity')
                            ->label('Qty')
                            ->numeric()
                            ->required(),

                        TextInput::make('unit')
                            ->label('Unit')
                            ->readOnly()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $supply = Supplies::find($data['supplies_id']);
                        $exists = BillOfMaterialItem::query()
                            ->where('bill_of_materials_id', $this->bom_id)
                            ->where('supplies_id', $data['supplies_id'])
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title('Item sudah ditambahkan sebelumnya.')
                                ->danger()
                                ->send();
                            return;
                        }

                        BillOfMaterialItem::create([
                            'bill_of_materials_id' => $this->bom_id,
                            'supplies_id' => $data['supplies_id'],
                            'quantity' => $data['quantity'],
                            'unit' => $data['unit'],
                            'category_supplies_id' => $supply?->category_supplies_id, 
                        ]);

                    })
                    ->modalHeading('Add Material'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        TextInput::make('quantity')->required()->numeric(),
                        TextInput::make('unit')->required(),
                    ]),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
