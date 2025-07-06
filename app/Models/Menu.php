<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_menus_id',
        'name',
        'price',
        'description',
        'image',
    ];

    public function categoryMenu()
    {
        return $this->belongsTo(CategoryMenu::class, 'category_menus_id');
    }

    public function billOfMaterial()
    {
        return $this->hasOne(\App\Models\BillOfMaterial::class, 'menus_id');
    }

}
