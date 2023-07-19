<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sendOperationsModel extends Model
{
    protected $fillable = [
        'accountId',
        'operationsAccrue',
        'operationsCancellation',
        'operationsDocument',
        'operationsPaymentDocument',
        'customOperation',
    ];

    use HasFactory;
}
