<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingMain extends Model
{
    protected $fillable = [
        'accountId',
        'TokenMoySklad',
        'companyId',
        'TokenUDS',
        'ProductFolder',
        'UpdateProduct',
        'Store',
    ];

    use HasFactory;
}
