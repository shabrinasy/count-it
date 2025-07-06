<?php

namespace App\Filament\Resources\CategoryMenuResource\Pages;

use App\Filament\Resources\CategoryMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryMenu extends CreateRecord
{
    protected static string $resource = CategoryMenuResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
