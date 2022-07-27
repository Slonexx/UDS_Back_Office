<?php

namespace App\Http\Controllers;

use App\Components\MsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ModifyProductController extends Controller
{
    
    public function createModifyProductMs($productMeta,$nameModify,$characters,$apiKey)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/variant";
        $client = new MsClient($apiKey);
        $characteristics = [];

        $idCharacter = $this->getCharacterByName($nameModify,$apiKey);
        if($idCharacter == null){
            $idCharacter = $this->createCharacterByName($nameModify,$apiKey);
        }

        foreach($characters as $character){
                $ch['id'] = $idCharacter;
                $ch['value'] = $character;
                array_push($characteristics, $ch);
        }

        $body = [
            'characteristics' => $characteristics,
             'product' => [
                 'meta' => $productMeta,
             ],
        ];
        //dd(json_encode($body));
        $client->post($url,$body);
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
