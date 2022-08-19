<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;

class StockProductService
{
    public function getProductStockMs($idNode,$storeHref,$apiKey)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/report/stock/all?".
        "filter=store=".$storeHref;
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        $count = 0;
        foreach($json->rows as $row){
            if($row->externalCode == $idNode){
                if ($row->stock > 0) {
                    $count = $row->stock;
                }
                break;
            }
        }
        return $count;
    }
}
