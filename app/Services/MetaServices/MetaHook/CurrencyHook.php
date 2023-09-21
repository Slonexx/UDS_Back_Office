<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class CurrencyHook
{

    private MsClient $msClient;

    public function __construct($ms)
    {
        $this->msClient = $ms;
    }

    public function getKzCurrency(): array
    {
        $json = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/currency?seacrh=тенге");
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
            return $this->createCurrency();
        } else return $foundedMeta;
    }

    public function createCurrency(): array
    {
        $uri = "https://api.moysklad.ru/api/remap/1.2/entity/currency";
        $currency = [
            "system" => true,
            "isoCode" => "KZT",
        ];
        $createdMeta =  $this->msClient->post($uri,$currency)->meta;

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
