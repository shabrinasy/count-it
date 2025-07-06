<?php

namespace App\Filament\Resources\BillOfMaterialResource\Pages;

use App\Filament\Resources\BillOfMaterialResource;
use App\Filament\Resources\BillOfMaterialItemResource\Widgets\BillOfMaterialItemWidget;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBillOfMaterial extends CreateRecord
{
    protected static string $resource = BillOfMaterialResource::class;

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

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('create')
                ->label('Save & Add Items')
                ->submit('create')
                ->keyBindings(['mod+s']),

            Actions\Action::make('done')
                ->label('Done')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->url(fn () => route('filament.admin.resources.bill-of-materials.index'))
                ->visible(fn () => filled($this->record?->id)),
        ];
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Bill of Material berhasil disimpan.')
            ->success()
            ->send();

        $this->halt(); 
    }

    protected function getRedirectUrl(): string
    {
        return url()->current(); 
    }
}
