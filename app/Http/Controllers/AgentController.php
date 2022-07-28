<?php

namespace App\Http\Controllers;

use App\Components\MsClient;
use App\Components\UdsClient;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    
    public function getAgentMs($apiKey){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        return $json;
    }

    public function getAgentUds($companyId, $apiKey){
        $url = "https://api.uds.app/partner/v2/customers";
        $client = new UdsClient($companyId,$apiKey);
        try{
            $json = $client->get($url);
        } catch (\Throwable $th) {
            dd($th);
        }
        
        return $json;
    }

    public function insertAgentsToUds(){

    }

    public function insertAgentsToMs(){
        
    }

}
