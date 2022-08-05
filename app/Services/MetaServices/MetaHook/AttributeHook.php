<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class AttributeHook
{
    public function getProductAttribute($nameAttribute,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes";
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json->rows as $row){
            if($row->name == $nameAttribute){
                $foundedMeta = $row->meta;
                break;
            }
        }
        return $foundedMeta;
    }
}
