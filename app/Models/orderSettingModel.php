<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class orderSettingModel extends Model
{
    protected $fillable = [
        'accountId',
        'creatDocument',
        'Organization',
        'PaymentDocument',
        'Document',
        'PaymentAccount',
    ];

    use HasFactory;
}
