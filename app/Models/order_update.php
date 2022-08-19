<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class order_update extends Model
{
    protected $fillable = [
        'accountId',
        'message',
    ];

    use HasFactory;
}
