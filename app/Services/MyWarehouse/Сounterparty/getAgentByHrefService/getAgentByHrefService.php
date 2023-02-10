<?php

namespace App\Services\MyWarehouse\Сounterparty\getAgentByHrefService;


use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;

class getAgentByHrefService
{
    public function getAgent(string $Key, string $href){
        $Client = new MsClient($Key);
        return $Client->get($href);
    }

    public function getAgentToObject(string $Key, string $href, string $Object){
        $Client = new MsClient($Key);
        return $Client->get($href)->$Object;
    }



}
