<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'orders_id',
        'menus_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'orders_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menus_id');
    }

    protected static function booted()
    {
        static::created(function (OrderItem $orderItem) {
            $menu = $orderItem->menu()->with('billOfMaterial.billOfMaterialItems.supplies')->first();

            if (! $menu?->billOfMaterial) {
                return; 
            }

            foreach ($menu->billOfMaterial->billOfMaterialItems as $item) {
                $supply = $item->supplies;

                if ($supply) {
                    $totalUsed = $item->quantity * $orderItem->quantity;

                    // Mengecek jika stok cukup
                    if ($supply->stock < $totalUsed) {
                        \Filament\Notifications\Notification::make()
                            ->title("Stok bahan '{$supply->name}' tidak mencukupi untuk menu '{$menu->name}'!")
                            ->danger()
                            ->send();

                        // Mencegah orderItem disimpan jika stok tidak cukup
                        throw new \Exception("Stok bahan '{$supply->name}' tidak mencukupi untuk menu '{$menu->name}'.");
                    }

                    // Kurangi stok jika cukup
                    $supply->decrement('stock', $totalUsed);
                }
            }
        });
    }
}
