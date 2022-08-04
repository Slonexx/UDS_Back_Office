<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class UomHook
{
    public function getUom($nameUom,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/uom";
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json->rows as $row){
            if($row->name == $nameUom){
                $foundedMeta = $row->meta;
                break;
            }
        }
        return [
            "meta" => $foundedMeta,
        ];
    }
}
