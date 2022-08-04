<?php

namespace App\Services;

use App\Components\MsClient;
use App\Components\UdsClient;

class ProductService
{
    private function getMs($apiKeyMs)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
    }

    private function getUds($companyId, $apiKeyUds)
    {
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);
        $json = $client->get($url);

//        $categoryIds = [];
//        foreach ($json->rows as $row) { --need recursive method
//
//            array_push($categoryIds, $row->id);
//        }

        //return ;
    }

    private function notAddedInUds($apiKeyMs,$apiKeyUds,$companyId)
    {
       // $productMs = $this->getMs($apiKeyMs);
       // $productUds =
            $this->getUds($companyId,$apiKeyUds);
    }

    private function notAddedInMs()
    {

    }

    public function insertToUds($data)
    {
        $this->notAddedInUds(
            $data['tokenMs'],
            $data['apiKeyUds'],
            $data['companyId']
        );
    }

    public function insertToMs($data)
    {

    }
}
