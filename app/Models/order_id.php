<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_id extends Model
{

    protected $fillable = [
        'accountId',
        'orderID',
    ];

    use HasFactory;
}
