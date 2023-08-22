<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class newProductModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',
        'ProductFolder',
        'unloading',
        'salesPrices',
        'promotionalPrice',
        'Store',
        'StoreRecord',
        'productHidden',
        'countRound',
    ];

}
