<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function getFormActions(): array
    {
        return [
           Action::make('create')
            ->label('Next')
            ->submit('create')
            ->keyBindings(['mod+s']),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $id = $this->record->id;
        return route('filament.admin.resources.purchase-items.create', 
        [
            'purchase_id' => $id
        ]);
    }
}
