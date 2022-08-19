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
            $foundedMeta = $row->meta;
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

        return $createdMeta;
    }
}
