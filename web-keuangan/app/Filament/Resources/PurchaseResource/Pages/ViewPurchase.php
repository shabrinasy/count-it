<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use App\Filament\Resources\PurchaseItemResource\Widgets\PurchaseItemWidget;
use Filament\Resources\Pages\Page;
use App\Models\Purchase;

class ViewPurchase extends Page
{
    protected static string $resource = PurchaseResource::class;

    protected static string $view = 'filament.resources.purchase-resource.pages.view-purchase';

    public $record;

    public function mount($record): void
    {
        $this->record = Purchase::findOrFail($record);
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\PurchaseItemResource\Widgets\PurchaseItemWidget::make([
                'record' => $this->record->id,
                'fromPage' => static::class,
            ]),
        ];
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }
}
