<?php

namespace App\Filament\Resources\CategorySuppliesResource\Pages;

use App\Filament\Resources\CategorySuppliesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategorySupplies extends ListRecords
{
    protected static string $resource = CategorySuppliesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
