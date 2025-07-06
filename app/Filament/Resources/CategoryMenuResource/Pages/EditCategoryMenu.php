<?php

namespace App\Filament\Resources\CategoryMenuResource\Pages;

use App\Filament\Resources\CategoryMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCategoryMenu extends EditRecord
{
    protected static string $resource = CategoryMenuResource::class;

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
