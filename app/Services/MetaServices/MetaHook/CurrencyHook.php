<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class CurrencyHook
{
    public function getKzCurrency($apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/currency?seacrh=тенге";
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json->rows as $row){
            $foundedMeta = [
                "meta" => [
                    "href" => $row->meta->href,
                    "metadataHref" =>$row->meta->metadataHref,
                    "type" => $row->meta->type,
                    "mediaType" => $row->meta->mediaType,
                    "uuidHref" => $row->meta->uuidHref,
                ],
            ];
            break;
        }
        if (is_null($foundedMeta)){
            return $this->createCurrency($apiKey);
        } else return $foundedMeta;
    }

    public function createCurrency($apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/currency";
        $client = new MsClient($apiKey);
        $currency = [
            "system" => true,
            "isoCode" => "KZT",
        ];
        $createdMeta = $client->post($uri,$currency)->meta;

        return [
            "meta" => [
                "href" => $createdMeta->href,
                "metadataHref" =>$createdMeta->metadataHref,
                "type" => $createdMeta->type,
                "mediaType" => $createdMeta->mediaType,
                "uuidHref" => $createdMeta->uuidHref,
            ],
        ];
    }
}
