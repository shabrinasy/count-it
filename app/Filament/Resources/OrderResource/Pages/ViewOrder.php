<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Filament\Resources\OrderItemResource\Widgets\OrderItemWidget;
use Filament\Resources\Pages\Page;
use App\Models\Order;

class ViewOrder extends Page
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.order-resource.pages.view-order';

    public $record;

    public function mount($record): void
    {
        $this->record = Order::findOrFail($record);
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Resources\OrderItemResource\Widgets\OrderItemWidget::make([
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
