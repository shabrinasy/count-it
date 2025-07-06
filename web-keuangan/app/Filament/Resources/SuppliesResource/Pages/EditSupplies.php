<?php

namespace App\Filament\Resources\SuppliesResource\Pages;

use App\Filament\Resources\SuppliesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSupplies extends EditRecord
{
    protected static string $resource = SuppliesResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
