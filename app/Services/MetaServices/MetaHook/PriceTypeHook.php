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
    public function getPriceType($namePrice): array
    {
        $json = $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype");
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

    private function createPriceType($namePrice){
        $url = "https://online.moysklad.ru/api/remap/1.2/context/companysettings/pricetype";
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
