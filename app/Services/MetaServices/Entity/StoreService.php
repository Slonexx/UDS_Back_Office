<?php

namespace App\Services\MetaServices\Entity;

use App\Components\MsClient;

class StoreService
{
    public function getStore($storeName,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/store?search=".$storeName;
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
            return $this->createStore($storeName,$apiKey);
        } else return $foundedMeta;
    }

    public function createStore($storeName,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/store";
        $client = new MsClient($apiKey);
        $store = [
            "name" => $storeName,
        ];
        $createdMeta = $client->post($uri,$store)->meta;

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
