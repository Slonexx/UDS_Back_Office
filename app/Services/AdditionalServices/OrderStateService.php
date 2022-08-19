<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Services\Settings\StateOrderSettings;

class OrderStateService
{
    private StateOrderSettings $stateOrderSettings;

    /**
     * @param StateOrderSettings $stateOrderSettings
     */
    public function __construct(StateOrderSettings $stateOrderSettings)
    {
        $this->stateOrderSettings = $stateOrderSettings;
    }


    public function getState($accountId,$statusFrom,$apiKey){

        $status = $this->stateOrderSettings->getStatusName($accountId,$statusFrom);

        if($status == null){
            return null;
        } else {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
            $client = new MsClient($apiKey);
            $jsonStates = $client->get($uri);
            $foundedState = null;
            foreach($jsonStates->states as $state){
                if($state->name == $status){
                    $foundedState = $state->meta;
                    break;
                }
            }
            return $foundedState;
        }
    }
}
