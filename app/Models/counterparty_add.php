<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class counterparty_add extends Model
{
    protected $fillable = [
        'tokenMC',
        'companyId',
        'tokenUDS',
    ];

    use HasFactory;
}
