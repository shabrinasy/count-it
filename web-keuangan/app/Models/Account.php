<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_account',
        'name_account',
        'balance',
        'type',
        'account_activity',
        'parent',
    ];
}
