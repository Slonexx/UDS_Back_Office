<?php

namespace App\Components;

use GuzzleHttp\Client;

class ImportDateHttpClient
{
    public $client;
    public function __construct()
    {
        $this->client = new Client([
            'auth' => ['sergey@smart_demo', 'Aa1234!!'],
            'base_uri' => 'https://online.moysklad.ru/api/remap/1.2/entity/assortment',
            'timeout'  => 2.0,
            'verify' => false,
        ]);
    }



}
