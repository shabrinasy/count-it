<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOfMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_of_materials_id', 
        'supplies_id', 
        'category_supplies_id',
        'quantity', 
        'unit',
    ];

    public function billOfMaterial()
    {
        return $this->belongsTo(BillOfMaterial::class, 'bill_of_materials_id');
    }

    public function supplies()
    {
        return $this->belongsTo(Supplies::class, 'supplies_id');
    }

    public function categorySupplies()
    {
        return $this->belongsTo(CategorySupplies::class, 'category_supplies_id');
    }
}


