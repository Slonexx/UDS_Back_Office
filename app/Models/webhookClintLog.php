<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class webhookClintLog extends Model
{
    protected $fillable = [
        'accountId',
        'message',
        'companyId',
    ];

    use HasFactory;
}
