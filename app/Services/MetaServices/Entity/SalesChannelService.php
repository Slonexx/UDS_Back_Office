<?php

namespace App\Services\MetaServices\Entity;

use App\Components\MsClient;

class SalesChannelService
{
    public function getSaleChannel($saleChannelName,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel?search=".$saleChannelName;
        $client = new MsClient($apiKey);
        $json = $client->get($uri);
        $foundedMeta = null;
        foreach($json->rows as $row){
            if ($row->name == $saleChannelName){
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
        }
        if (is_null($foundedMeta)){
            return $this->createSaleChannel($saleChannelName,$apiKey);
        } else return $foundedMeta;
    }

    public function createSaleChannel($saleChannelName,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel";
        $client = new MsClient($apiKey);
        $saleChannel = [
            "name" => $saleChannelName,
            "type" => "MARKETPLACE",
        ];
        $createdMeta = $client->post($uri,$saleChannel)->meta;

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
