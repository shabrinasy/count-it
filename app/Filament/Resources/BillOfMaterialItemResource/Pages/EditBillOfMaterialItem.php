<?php

namespace App\Filament\Resources\BillOfMaterialItemResource\Pages;

use App\Filament\Resources\BillOfMaterialItemResource;
use App\Filament\Resources\BillOfMaterialItemResource\Widgets\BillOfMaterialItemWidget;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditBillOfMaterialItem extends EditRecord
{
    protected static string $resource = BillOfMaterialItemResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\DeleteAction::make(),
    //     ];
    // }

    // protected function getFooterWidgets(): array
    // {
    //     if (! filled($this->record?->id)) {
    //         return []; 
    //     }

    //     return [
    //         BillOfMaterialItemWidget::make([
    //             'bom_id' => $this->record->id,
    //             'menus_id' => $this->record->menus_id,
    //         ]),
    //     ];
    // }

    // public function getFooterWidgetsColumns(): int|array
    // {
    //     return 1;
    // }

    // protected function getFormActions(): array
    // {
    //     return [
    //         Actions\Action::make('create')
    //             ->label('Save & Add Items')
    //             ->submit('create')
    //             ->keyBindings(['mod+s']),

    //         Actions\Action::make('done')
    //             ->label('Done')
    //             ->color('success')
    //             ->icon('heroicon-o-check-circle')
    //             ->url(fn () => route('filament.admin.resources.bill-of-materials.index'))
    //             ->visible(fn () => filled($this->record?->id)),
    //     ];
    // }

    // protected function afterCreate(): void
    // {
    //     Notification::make()
    //         ->title('Bill of Material berhasil disimpan.')
    //         ->success()
    //         ->send();

    //     $this->halt(); 
    // }

    // protected function getRedirectUrl(): string
    // {
    //     return url()->current(); 
    // }
}
