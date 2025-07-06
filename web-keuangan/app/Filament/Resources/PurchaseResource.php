<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Storage;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\DB;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembelian';

    protected static ?string $pluralLabel = 'Pembelian'; 
    
    protected static ?string $title = 'Pembelian'; 
    
    public static function getLabel(): string
    {
        return 'Pembelian'; // Label ini akan digunakan untuk bagian header
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->default(Purchase::getKode())  // Set default value here
                    ->disabled(),
                Forms\Components\DatePicker::make('date')
                    ->default(now())
                    ->required()
                    ->label('Transaction Date')
                    ->maxDate(now()),
                Forms\Components\Select::make('suppliers_id')
                    ->relationship('supplier', 'name', function ($query) {
                        $query->where('status', '1');
                    })
                    ->required()
                    ->createOptionForm(
                        \App\Filament\Resources\SupplierResource::getForm()
                    )
                    ->reactive()
                    ->afterStateUpdated(function($state, Forms\Set $set){
                        $supplier = \App\Models\Supplier::find($state);
                        $set('pic_phone', $supplier->pic_phone ?? null);
                    }),
                Forms\Components\TextInput::make('pic_phone')
                    ->label('Phone')
                    ->disabled(),
                Forms\Components\FileUpload::make('file')
                    ->disk('public')
                    ->directory('purchase-files')
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                    ->previewable(true)
                    ->required(),
                Forms\Components\TextInput::make('notes')
                    ->label('Note'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('d F Y')
                    ->sortable()
                    ->label('Transaction Date'),
                Tables\Columns\TextColumn::make('total_payment') // Total Payment
                    ->label('Total Payment')
                    ->state(function ($record) {
                        $total = optional($record->purchaseItems)->sum(fn ($item) => $item->quantity * $item->price) ?? 0;
                        return 'IDR ' . number_format($total, 0, ',', '.');
                    }),
                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.purchases.detail', ['record' => $record->id]))
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
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'detail' => Pages\ViewPurchase::route('/{record}/detail'),
        ];
    }

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['pemilik', 'pembelian']);
    }

}
