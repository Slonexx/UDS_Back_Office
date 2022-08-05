<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class PriceTypeHook
{
    public function getPriceType($namePrice,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype";
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json as $price){
            if($price->name == $namePrice){
                $foundedMeta = $price->meta;
                break;
            }
        }
        return [
            "meta" => $foundedMeta,
        ];
    }
}
