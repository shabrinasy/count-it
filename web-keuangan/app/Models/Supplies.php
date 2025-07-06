<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplies extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_supplies_id',
        'stock',
        'unit',
    ];

    public function categorySupplies()
    {
        return $this->belongsTo(CategorySupplies::class, 'category_supplies_id');
    }
}
