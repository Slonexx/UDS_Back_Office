<?php

namespace App\Http\Controllers;

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

    public function url_ms(): string
    {
        return 'https://online.moysklad.ru/api/remap/1.2/entity/';
    }


}
