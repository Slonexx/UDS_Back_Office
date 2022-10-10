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
    private function createFactureout($Setting, $SettingBD, $OldBody, $externalCode){
        if ($SettingBD->operationsDocument == 0 or $SettingBD->operationsDocument == null) {

        } else {
            try {
                if ($SettingBD->operationsDocument == '2' or $SettingBD->operationsDocument == 2) {
                    $body = [
                        'demands' => [  0 => [ 'meta' => [
                            'href' => $OldBody->meta->href,
                            'metadataHref' => $OldBody->meta->metadataHref,
                            'type' => $OldBody->meta->type,
                            'mediaType' => $OldBody->meta->mediaType,
                        ] ] ] ];

                    $urlFacture = 'https://online.moysklad.ru/api/remap/1.2/entity/factureout';
                    $client = new MsClient($Setting->TokenMoySklad);
                    $postBodyCreateFactureout = $client->post($urlFacture, $body);
                }
            } catch (\Throwable $e) {
            }
        }
    }

    public function operations(Request $request){
        $data = $request->validate([
            "accountId" => 'required|string',
            "objectId" => 'required|string',
            "user" => "required|string",
            "cashier_id" => "required|string",
            "cashier_name" => "required|string",
            "receipt_total" => "required|string",
            "receipt_cash" => "required|string",
            "receipt_points" => "required|string",
            "receipt_skipLoyaltyTotal" => "required|string",
        ]);
        if ( strlen(str_replace(' ','',$data['user']) ) > 6) {
            $data['code'] = null;
            $data['phone'] = str_replace("+7", '', $data['user']);
            $data['phone'] = '+7' . str_replace(" ", '', $data['phone']);
        } else {
            $data['code'] = $data['user'];
            $data['phone'] = null;
        }

        if ( $data['receipt_points'] == "undefined" ) $data['receipt_points'] = '0';
        if ( $data['receipt_skipLoyaltyTotal'] == "undefined" or $data['receipt_skipLoyaltyTotal'] == "0" ) $data['receipt_skipLoyaltyTotal'] = null;

        $url = 'https://api.uds.app/partner/v2/operations';
        $Setting = new getSettingVendorController($data['accountId']);
        $SettingBD = new getSetting();
        $SettingBD = $SettingBD->getSendSettingOperations($data['accountId']);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $body = [
            'code' => $data['code'],
            'participant' => [
                'uid' => null,
                'phone' => $data['phone'],
            ],
            'nonce' => null,
            'cashier' => [
                'externalId' => $data['cashier_id'],
                'name' => $data['cashier_name'],
            ],
            'receipt' => [
                'total' => $data['receipt_total'],
                'cash' => (string) round($data['receipt_cash'],2),
                'points' => $data['receipt_points'],
                'number' => null,
                'skipLoyaltyTotal' => $data['receipt_skipLoyaltyTotal'],
            ],
            'tags' => null
        ];

        //try {
        $post = $Client->post($url, $body);

        $urlMC = 'https://online.moysklad.ru/api/remap/1.2/entity/demand/' . $data['objectId'];
        $ClientMC = new ClientMC($urlMC, $Setting->TokenMoySklad);
        $OldBody = $ClientMC->requestGet();

        $setPositions = $this->Positions($post, $data['receipt_skipLoyaltyTotal'], $OldBody, $Setting);
        $setAttributes = $this->Attributes($post, $Setting);

        $OldBody->externalCode = $post->id;
        $putBody = $ClientMC->requestPut([
            'externalCode'=>(string) $post->id,
            'positions'=> $setPositions,
            'attributes' => $setAttributes,
        ]);
        $this->createFactureout($Setting, $SettingBD, $putBody, (string) $post->id);
        $post = [
            'code' => 200,
            'id' => $post->id,
            'points' => $post->points,
            'total' => $post->total,
            'message' => 'The operation was successful',
        ];

        /*} catch ( \Throwable $e){
            dd($e);

            /*$post = [
               'code' =>  $e->getCode(),
               'message' =>  $e->getMessage(),
            ];
        }*/

        return response()->json($post);
    }
}
