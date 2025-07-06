<?php

namespace App\Filament\Resources\CategoryMenuResource\Pages;

use App\Filament\Resources\CategoryMenuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCategoryMenus extends ListRecords
{
    protected static string $resource = CategoryMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
