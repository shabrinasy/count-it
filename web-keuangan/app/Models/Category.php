<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_category',
        'is_expense',
        'account_id',
    ];

    // App\Models\Category.php

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }


}
