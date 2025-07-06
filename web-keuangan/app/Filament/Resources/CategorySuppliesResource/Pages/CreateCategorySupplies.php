<?php

namespace App\Filament\Resources\CategorySuppliesResource\Pages;

use App\Filament\Resources\CategorySuppliesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategorySupplies extends CreateRecord
{
    protected static string $resource = CategorySuppliesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
