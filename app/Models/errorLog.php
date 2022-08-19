<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class errorLog extends Model
{
    protected $fillable = [
        'accountId',
        'ErrorMessage',
    ];

    use HasFactory;
}
