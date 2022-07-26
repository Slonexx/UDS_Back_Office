<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class goods extends Model
{

    protected $fillable = [
        'id_MC',
        'name',
        'price',
        'stock',
        'offerPrice_type',
        'offerSkipLoyalty',
        'offerPrice',
        'article',
        'description',
        'photos',
        'measurement',
        'type_CATEGORY',

    ];

    use HasFactory;
}
