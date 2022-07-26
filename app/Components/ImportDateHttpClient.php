<?php

namespace App\Components;

use GuzzleHttp\Client;

class ImportDateHttpClient
{
    public $client;
    public function __construct($email, $password, $base_url)
    {
        $this->client = new Client([
            'auth' => [$email, $password],
            'base_uri' => 'https://online.moysklad.ru/api/remap/1.2/'.$base_url,
            'timeout'  => 2.0,
            'verify' => false,
        ]);
    }





}
