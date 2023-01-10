<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class mainURL extends Controller
{

    public function url_host(): string
    {
        return 'https://dev.smartuds.kz/';
    }

    public function url_uds(): string
    {
        return 'https://api.uds.app/';
    }


}
