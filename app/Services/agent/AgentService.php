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

    private function getUds($companyId, $apiKeyUds)
    {
        $url = "https://api.uds.app/partner/v2/customers";
        $client = new UdsClient($companyId,$apiKeyUds);
        return $client->get($url);
    }

    private function notAddedInMs($apiKeyMs,$apiKeyUds, $companyId)
    {
        $customersFromUds = $this->getUds($companyId,$apiKeyUds);
        $customersFromMs = $this->getMs($apiKeyMs);

        $count = 0;
        foreach ($customersFromUds->rows as $customerFromUds){
            $currId = $customerFromUds->participant->id;
            if (!in_array($currId,$customersFromMs)){
                $this->createAgent($apiKeyMs,$customerFromUds);
                $count++;
            }
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

}
