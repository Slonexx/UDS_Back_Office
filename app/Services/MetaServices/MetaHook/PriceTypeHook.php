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
        $count = 0;
        foreach($json as $price){
            if($price->name == $namePrice){
                $foundedMeta = $price->meta;
                break;
            }
            $count++;
        }

        if ($foundedMeta == null){
            $meta = $this->createPriceType($namePrice,$apiKey)[$count]->meta;
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
        $json = $client->get($url);
        $item = $json[0];
        $body = [
            0 => [
                "meta" => [
                    "href" => $item->meta->href,
                    "type" => $item->meta->type,
                    "mediaType" => $item->meta->mediaType,
                ],
                "name" => $item->name,
            ],
            1 => [
                "name" => $namePrice,
            ],
        ];
        return $client->post($url, $body);
    }

}
