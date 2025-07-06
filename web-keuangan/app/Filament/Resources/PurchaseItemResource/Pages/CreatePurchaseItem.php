<?php

namespace App\Filament\Resources\PurchaseItemResource\Pages;

use App\Filament\Resources\PurchaseItemResource;
use App\Filament\Resources\PurchaseResource;
use App\Filament\Resources\PurchaseItemResource\Widgets\PurchaseItemWidget;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Supplies;

class CreatePurchaseItem extends CreateRecord
{
    protected static string $resource = PurchaseItemResource::class;

    protected function afterCreate(): void
    {
        $purchaseItem = $this->record;
        $supply = Supplies::find($purchaseItem->supplies_id);

        if ($supply) {
            $beratTotal = $purchaseItem->quantity * $purchaseItem->actual_weight;
            $supply->increment('stock', $beratTotal);
        }
    }
    
    protected function getFormActions(): array
    {
        return [
           Action::make('create')
            ->label('Save')
            ->submit('create')
            ->keyBindings(['mod+s']),
            Action::make('done')
            ->label('Done')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->url(fn () => route('filament.admin.resources.purchases.index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $id = $this->record->purchases_id;
        return route('filament.admin.resources.purchase-items.create', 
        [
            'purchase_id' => $id
        ]);
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getFooterWidgets(): array
    {
        return [
           PurchaseItemWidget::make([
            'record' => request('purchase_id'),
            'fromPage' => static::class,
           ]),
        ];
    }

}
