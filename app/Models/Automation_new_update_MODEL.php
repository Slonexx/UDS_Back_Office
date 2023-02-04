<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Automation_new_update_MODEL extends Model
{

    protected $fillable = [
        'accountId',

        'activateAutomation',
        'statusAutomation',
        'projectAutomation',
        'saleschannelAutomation',

        'automationDocument',
        'add_automationOrganization',
        'add_automationPaymentDocument',
        'add_saleschannelAutomation',
        'add_projectAutomation',
    ];

    use HasFactory;
}
