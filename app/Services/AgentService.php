<?php

namespace App\Services;

use App\Components\MsClient;
use App\Components\UdsClient;

class AgentService
{

    public function getAgentMs($apiKey)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        return $json;
    }

    private function getAgentUds($companyId, $apiKey)
    {
        $url = "https://api.uds.app/partner/v2/customers";
        $client = new UdsClient($companyId,$apiKey);
        try{
            $json = $client->get($url);
        } catch (\Throwable $th) {
            dd($th);
        }

        return $json;
    }

    private function notAddedAgentsInUds()
    {

    }

    private function notAddedAgentsInMs()
    {

    }

    public function insertAgentsToUds()
    {

    }

    public function insertAgentsToMs()
    {

    }

}
