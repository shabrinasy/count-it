<?php

namespace App\Filament\Resources\CategorySuppliesResource\Pages;

use App\Filament\Resources\CategorySuppliesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategorySupplies extends EditRecord
{
    protected static string $resource = CategorySuppliesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
