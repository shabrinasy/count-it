<?php

namespace App\Filament\Resources\SuppliesResource\Pages;

use App\Filament\Resources\SuppliesResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSupplies extends CreateRecord
{
    protected static string $resource = SuppliesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
