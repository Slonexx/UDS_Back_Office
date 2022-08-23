<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agent_503 extends Model
{
    protected $fillable = [
        'accountId',
        'url',
        'offset',
    ];

    use HasFactory;
}
