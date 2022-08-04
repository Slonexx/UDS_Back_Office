<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;

class ModifyProductService
{

    public function createModifyProductMs($productMeta,$nameModify,$character,$apiKey)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/variant";
        $client = new MsClient($apiKey);

        $idCharacter = $this->getCharacterByName($nameModify,$apiKey);
        if($idCharacter == null){
            $idCharacter = $this->createCharacterByName($nameModify,$apiKey);
        }

        $body = [
            'name' => $nameModify,
            'characteristics' => [
                0 => [
                    'id' => $idCharacter,
                    'value' => $character,
                ]
            ],
            'product' => [
                'meta' => $productMeta,
            ],
        ];

        try {
            $client->post($url,$body);
        } catch (\Throwable $th) {
            dd($th);
        }

    }

    private function createCharacterByName($nameCharacter,$apiKey){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics";
        $client = new MsClient($apiKey);
        $body = [
            "name" => $nameCharacter,
        ];
        return $client->post($url,$body)->id;
    }

    private function getCharacterByName($nameCharacter,$apiKey){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/variant/metadata/characteristics";
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        $foundedId = null;
        foreach($json->characteristics as $character){
            if($character->name == $nameCharacter){
                $foundedId = $character->id;
                break;
            }
        }
        return $foundedId;
    }

    public function sendModifyUds()
    {

    }

}
