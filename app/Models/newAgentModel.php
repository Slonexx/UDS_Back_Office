<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class newAgentModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'accountId',

        'unloading',
        'examination',
        'email',
        'gender',
        'birthDate',

        'url',
        'offset',

        'countRound',
    ];

}
