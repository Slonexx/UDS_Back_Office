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

        if ($foundedMeta == null){
            $meta = $this->createPriceType($namePrice,$apiKey)->meta;
            return [
                "meta" => $meta,
            ];
        } else {
            return [
                "meta" => $foundedMeta,
            ];
        }
    }

    private function createPriceType($namePrice,$apiKey){
        $url = "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype";
        $client = new MsClient($apiKey);
        $body = [
            "name" => $namePrice,
        ];
        return $client->post($url, $body);
    }

}
