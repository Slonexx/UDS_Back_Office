<?php

namespace App\Services\order;

use App\Components\MsClient;
use App\Components\UdsClient;

class OrderService
{
    private function getMs($apiKey)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder";
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        return $json;
    }

    private function getUds($companyId, $apiKey)
    {
        $url = "";
        $client = new UdsClient($companyId,$apiKey);
        $json = $client->get($url);
        return $json;
    }

    private function notAddedInUds()
    {

    }

    private function notAddedInMs()
    {

    }

    public function insertToUds()
    {

    }

    public function insertToMs()
    {

    }
}
