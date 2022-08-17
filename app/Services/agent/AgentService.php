<?php

namespace App\Services\agent;

use App\Components\MsClient;
use App\Components\UdsClient;

class AgentService
{

    private function getMs($apiKeyMs): array
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKeyMs);
        $json = $client->get($url);
        $customerIds = [];
        foreach ($json->rows as $row){
            array_push($customerIds,$row->externalCode);
        }
        return $customerIds;
    }

    private function getUds($url,$companyId, $apiKeyUds)
    {
         //= "https://api.uds.app/partner/v2/customers";
        $client = new UdsClient($companyId,$apiKeyUds);
        return $client->get($url);
    }

    private function notAddedInMs($apiKeyMs,$apiKeyUds, $companyId)
    {

       // $customersFromMs = $this->getMs($apiKeyMs);

        $count = 0;
        $offset = 0;
        set_time_limit(3600);
        while ($this->haveRowsInResponse($url,$offset,$companyId,$apiKeyUds)){
            $customersFromUds = $this->getUds($url,$companyId,$apiKeyUds);
            foreach ($customersFromUds->rows as $customerFromUds){
                //dd($customerFromUds);
                $currId = $customerFromUds->participant->id;
                if (!$this->isAgentExistsMs($currId,$apiKeyMs)){
                    $this->createAgent($apiKeyMs,$customerFromUds);
                    $count++;
                }
            }
            $offset += 50;
        }


        return [
            "message" => "Inserted customers: ".$count,
        ];
    }

    public function insertToMs($data)
    {
       return $this->notAddedInMs(
           $data['tokenMs'],
           $data['apiKeyUds'],
           $data['companyId']
       );
    }

    private function createAgent($apiKeyMs,$customer)
    {

        $agent = [
            "name" => $customer->displayName,
            "companyType" => "individual",
            "externalCode" => "".$customer->participant->id,
        ];

        if ($customer->email != null){
            $agent["email"] = $customer->email;
        }

        if ($customer->phone != null){
            /*$cellPhone = "  ".$customer->phone;
            $toPhone = sprintf("%s %s %s",
                substr($cellPhone, 2, 3),
                substr($cellPhone, 5, 3),
                substr($cellPhone, 8));*/
            $agent["phone"] = $customer->phone;
        }

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKeyMs);
        $client->post($url,$agent);
    }

    private function haveRowsInResponse(&$url,$offset,$companyId,$apiKeyUds,$nodeId=0): bool
    {
        $url = "https://api.uds.app/partner/v2/customers?max=50&offset=".$offset;
        if ($nodeId > 0){
            $url = $url."&nodeId=".$nodeId;
        }
        $client = new UdsClient($companyId,$apiKeyUds);
        $json = $client->get($url);
        return count($json->rows) > 0;
    }

    private function isAgentExistsMs($nodeId, $apiKeyMs): bool
    {
        $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode=".$nodeId;
        //dd($urlToFind);
        $client = new MsClient($apiKeyMs);
        $json = $client->get($urlToFind);
        return ($json->meta->size > 0);
    }

}
