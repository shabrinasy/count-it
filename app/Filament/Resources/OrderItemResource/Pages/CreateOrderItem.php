<?php

namespace App\Filament\Resources\OrderItemResource\Pages;

use App\Filament\Resources\OrderItemResource;
use App\Filament\Resources\OrderItemResource\Widgets\OrderItemWidget;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateOrderItem extends CreateRecord
{
    protected static string $resource = OrderItemResource::class;

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
            ->url(fn () => route('filament.admin.resources.orders.index')),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $id = $this->record->orders_id;
        return route('filament.admin.resources.order-items.create', 
        [
            'order_id' => $id
        ]);
    }

    public function getFooterWidgetsColumns(): int|string|array
    {
        return 1;
    }

    public function getFooterWidgets(): array
    {
        return [
           OrderItemWidget::make(
            [
            'record' => request('order_id'),
            'fromPage' => static::class,
           ]
           ),
        ];
    }

}
