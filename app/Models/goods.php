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
        'offerPrice_type',
        'offerPrice',
        'article',
        'description',

    ];

    use HasFactory;
}
