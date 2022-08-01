<?php

namespace App\Services;

use App\Components\MsClient;
use App\Components\UdsClient;

class AgentService
{

    private function getMs($apiKeyMs)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
    }

    private function getUds($companyId, $apiKeyUds)
    {
        $url = "https://api.uds.app/partner/v2/customers";
        $client = new UdsClient($companyId,$apiKeyUds);
        return $client->get($url);
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
