<?php

namespace App\Services\MetaServices\MetaHook;

use App\Components\MsClient;

class UomHook
{

    private MsClient $msClient;

    public function __construct($ms)
    {
        $this->msClient = $ms;
    }

    public function getUom($nameUom): array
    {
        $json = $this->msClient->get("https://online.moysklad.ru/api/remap/1.2/entity/uom");
        $foundedMeta = null;
        foreach($json->rows as $row){
            if($row->name == $nameUom){
                $foundedMeta = $row->meta;
                break;
            }
        }
        return [
            "meta" => $foundedMeta,
        ];
    }
}
