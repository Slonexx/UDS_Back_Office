<?php

namespace App\Http\Controllers;

class mainURL extends Controller
{

    public function url_host(): string
    {
        return 'https://smartuds.kz/';
    }

    public function url_uds(): string
    {
        return 'https://api.uds.app/';
    }

    public function url_ms(): string
    {
        return 'https://api.moysklad.ru/api/remap/1.2/entity/';
    }

    public function me_url_host(): string
    {
        return 'https://smartuds.kz/';
    }

}
