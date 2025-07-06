<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillOfMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'menus_id',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menus_id');
    }

    public function billOfMaterialItems()
    {
        return $this->hasMany(BillOfMaterialItem::class, 'bill_of_materials_id');
    }
}
