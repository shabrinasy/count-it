<?php

namespace App\Filament\Resources\BillOfMaterialResource\Pages;

use App\Filament\Resources\BillOfMaterialResource;
use App\Filament\Resources\BillOfMaterialItemResource\Widgets\BillOfMaterialItemWidget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBillOfMaterial extends EditRecord
{
    protected static string $resource = BillOfMaterialResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFooterWidgets(): array
    {
        if (! filled($this->record?->id)) {
            return []; 
        }

        return [
            BillOfMaterialItemWidget::make([
                'bom_id' => $this->record->id,
                'menus_id' => $this->record->menus_id,
            ]),
        ];
    }

    public function getFooterWidgetsColumns(): int|array
    {
        return 1;
    }
}
