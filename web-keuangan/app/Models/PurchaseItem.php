<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchases_id',
        'supplies_id',
        'quantity',
        'price',
        'unit_purchase',
        'actual_weight',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class, 'purchases_id');
    }

    public function supplies()
    {
        return $this->belongsTo(Supplies::class, 'supplies_id');
    }
}
