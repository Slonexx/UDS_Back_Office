<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;

class CustomEntityService
{
    public function createCustomEntity($apiKey, $entityName, $values): void
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/customentity";
        $client = new MsClient($apiKey);
        $bodyEntity = [
            "name" => $entityName,
        ];
        $entity = $client->post($uri,$bodyEntity);

        foreach($values as $val){
            $this->createEntityElement($apiKey,$entity->id,$val);
        }

    }

    public function createEntityElement($apiKey,$entityId,$elementName): void
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/customentity/".$entityId;
        $client = new MsClient($apiKey);
        $bodyEntityEl = [
            "name" => $elementName,
        ];
        $client->post($uri,$bodyEntityEl);
    }
}
