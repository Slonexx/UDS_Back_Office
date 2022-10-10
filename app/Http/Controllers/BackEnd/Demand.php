<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class Demand extends Controller
{


    public function DemandObject($accountId, $entity, $objectId){
        $ObjectController = new ObjectController();
        $SettingBD = new getSetting();
        $SettingBD = $SettingBD->getSendSettingOperations($accountId);
        $Setting = new getSettingVendorController($accountId);

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/$entity/$objectId";
        $BodyMC = new ClientMC($url, $Setting->TokenMoySklad);
        $href = $BodyMC->requestGet()->agent->meta->href;
        $agentId = new ClientMC($href, $Setting->TokenMoySklad);
        $agentId = $agentId->requestGet();
        //Может и не быть
        if (property_exists($agentId, 'phone')) {
            $agentId = ['externalCode' => $agentId->externalCode, 'phone' => $agentId->phone,];
        } else {
            $agentId = [ 'externalCode' => $agentId->externalCode, 'phone' => null, 'dontPhone' => true, ];
            $StatusCode = 402;
            $message = 'Отсутствует номер телефона у данного контрагента';
            return [ 'StatusCode' => $StatusCode,  'message' => $message, ];
        }

        $externalCode = $BodyMC->requestGet()->externalCode;
        $Clint = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        try {
            $data = $ObjectController->newPostOperations($accountId, $Clint, $externalCode, $agentId);
            if ($data['status']) { $StatusCode = "200"; $message = $data['data'];
            } else {
                $StatusCode = "404";
                $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($objectId, $Setting);
                $availablePoints = $ObjectController->AgentMCID($href,  $Setting);
                $phone = $ObjectController->AgentMCPhone($href, $Setting);
                $operationsAccrue = $SettingBD->operationsAccrue;
                $operationsCancellation = $SettingBD->operationsCancellation;
                if ( $SettingBD->operationsAccrue == null ) $operationsAccrue = 0;
                if ( $SettingBD->operationsCancellation == 1 ){ $availablePoints = 0;
                } else  $operationsCancellation = 0;

                $message = [
                    'total' => $info_total_and_SkipLoyaltyTotal['total'],
                    'SkipLoyaltyTotal' => $info_total_and_SkipLoyaltyTotal['SkipLoyaltyTotal'],
                    'availablePoints' => $availablePoints,
                    'points' => 0,
                    'phone' => $phone,
                    'operationsAccrue' => (int) $operationsAccrue,
                    'operationsCancellation' => (int) $operationsCancellation,
                ];
            }
        } catch (ClientException $exception) {

        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];

    }

    private function TotalAndSkipLoyaltyTotal($objectId, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/demand/'.$objectId;
        $Clinet = new MsClient($Setting->TokenMoySklad);
        $bodyOrder = $Clinet->get($url);
        $sum = $bodyOrder->sum / 100;
        $SkipLoyaltyTotal = 0;
        $href = $bodyOrder->positions->meta->href;
        $BodyPositions = $Clinet->get($href)->rows;
        //ВОЗМОЖНОСТЬ СДЕЛАТЬ КОСТОМНЫЕ НАЧИСЛЕНИЕ
        foreach ($BodyPositions as $item){
            $url_item = $item->assortment->meta->href;
            $body = $Clinet->get($url_item)->attributes;
            $BonusProgramm = false;
            foreach ($body as $body_item){
                if ('Не применять бонусную программу (UDS)' == $body_item->name){
                    $BonusProgramm = $body_item->value;
                    break;
                }
            }
            if ( $BonusProgramm == true ){
                $price = ( $item->quantity * $item->price - ($item->quantity * $item->price * ($item->discount / 100)) ) / 100;
                $SkipLoyaltyTotal = $SkipLoyaltyTotal + $price;
            }

        }

        return [
            'total' => $sum,
            'SkipLoyaltyTotal' => $SkipLoyaltyTotal,
        ];
    }

}
