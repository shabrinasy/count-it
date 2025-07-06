<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillOfMaterialItemResource\Pages;
use App\Filament\Resources\BillOfMaterialItemResource\RelationManagers;
use App\Models\BillOfMaterialItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BillOfMaterialItemResource extends Resource
{
    protected static ?string $model = BillOfMaterialItem::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillOfMaterialItems::route('/'),
            'create' => Pages\CreateBillOfMaterialItem::route('/create'),
            'edit' => Pages\EditBillOfMaterialItem::route('/{record}/edit'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'pembelian', 'penjualan']);
    }

}
