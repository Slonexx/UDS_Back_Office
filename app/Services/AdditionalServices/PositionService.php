<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;

class PositionService
{

    public function setPositionReserve($orderId, $positionId, $quantityReserve,$apiKey)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/".$orderId."/positions"."/".$positionId;
        $client = new MsClient($apiKey);
        $bodyReserve = [
            "reserve" => $quantityReserve,
        ];
        $client->put($uri,$bodyReserve);
    }

}
