<?php

namespace App\Filament\Resources\BillOfMaterialItemResource\Pages;

use App\Filament\Resources\BillOfMaterialItemResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBillOfMaterialItems extends ListRecords
{
    protected static string $resource = BillOfMaterialItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
