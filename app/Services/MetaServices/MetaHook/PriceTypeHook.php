<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class PriceTypeHook
{
    private MsClient $msClient;

    public function __construct($ms)
    {
        $this->msClient = $ms;
    }
    public function getPriceType($namePrice, $id = null): array
    {
        $json = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype");
        $foundedMeta = null;
        $count = 0;

        foreach($json as $price){
            if($price->id == $id){
                $foundedMeta = $price->meta;
                break;
            }
            $count++;
        }
        if ($foundedMeta == null)
        foreach($json as $price){
            if($price->name == $namePrice){
                $foundedMeta = $price->meta;
                break;
            }
            $count++;
        } else
            return [
                "meta" => $foundedMeta,
            ];


        if ($foundedMeta == null){
            $meta = $this->createPriceType($namePrice)[$count]->meta;
            return [
                "meta" => $meta,
            ];
        } else {
            return [
                "meta" => $foundedMeta,
            ];
        }
    }

    public function getPriceTypeFirst($namePrice): array
    {
        $json = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype");
        return [
            "meta" => $json[0]->meta,
        ];
    }
    private function createPriceType($namePrice){
        $url = "https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype";
        $json =  $this->msClient->get($url);
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
        return  $this->msClient->post($url, $body);
    }

}
