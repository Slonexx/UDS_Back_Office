<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Models\errorLog;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ObjectController extends Controller
{
    public function CounterpartyObject(Request $request, $accountId, $entity, $objectId){

        $UDSURL = "https://api.uds.app/partner/v2/customers/";

        $cfg = new cfg();
        $Setting = new getSettingVendorController($accountId);

        $urlCounterparty = $cfg->moyskladJsonApiEndpointUrl."/entity/$entity/$objectId";
        $BodyMC = new ClientMC($urlCounterparty, $Setting->TokenMoySklad);

        $externalCode =  $BodyMC->requestGet()->externalCode;

        $body = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $last = $body->get($UDSURL.$externalCode);

        return response()->json(
            $last,201);

    }


    public function CustomerOrderEditObject($accountId, $entity, $objectId){

        $UDSURL = "https://api.uds.app/partner/v2/goods-orders/";

        $cfg = new cfg();
        $Setting = new getSettingVendorController($accountId);

        $urlCounterparty = $cfg->moyskladJsonApiEndpointUrl."/entity/$entity/$objectId";
        $BodyMC = new ClientMC($urlCounterparty, $Setting->TokenMoySklad);
        $href = $BodyMC->requestGet()->agent->meta->href;
        $agentId = new ClientMC($href, $Setting->TokenMoySklad);
        $agentId = $agentId->requestGet();
        //Может и не быть
        if (property_exists($agentId, 'phone')) {
            $agentId = [
                'externalCode' => $agentId->externalCode,
                'phone' => $agentId->phone,
            ];
        } else {
            $agentId = [
                'externalCode' => $agentId->externalCode,
                'phone' => null,
                'dontPhone' => true,
            ];
            $StatusCode = 402;
            $message = 'Отсутствует номер телефона у данного контрагента';
            return [
                'StatusCode' => $StatusCode,
                'message' => $message,
            ];
        }

        $externalCode = $BodyMC->requestGet()->externalCode;
        $Clint = new UdsClient($Setting->companyId, $Setting->TokenUDS);

        try {
            $body = $Clint->get($UDSURL.$externalCode);
            $purchase = $body->purchase;
            $StatusCode = "200";
            $id = $body->id;
            $cashBack = $purchase->cashBack;
            $points = $purchase->points;
            $state = $body->state;
            $icon = "";
            if ($state == "NEW") $icon = '<i class="fa-solid fa-circle-exclamation text-primary">  <span class="text-dark">НОВЫЙ</span> </i>';
            if ($state == "COMPLETED") $icon = '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Завершённый</span> </i>';
            if ($state == "DELETED") $icon = '<i class="fa-solid fa-circle-xmark text-danger"> <span class="text-dark">Отменённый</span> </i>';

            $message = [
                'id'=> $id,
                'BonusPoint'=>  $cashBack,
                'points'=> $points,
                'state'=> $state,
                'icon'=> $icon,
                'info'=> 'Order',
            ];
        } catch (ClientException $exception) {
            $data = $this->newPostOperations($accountId, $Clint, $externalCode, $agentId);
            if ($data['status']) {
                $StatusCode = "200";
                $message = $data['data'];
            } else {
                $StatusCode = "404";
                $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($objectId, $Setting);
                $SettingBD = new getSetting();
                $SettingBD = $SettingBD->getSendSettingOperations($accountId);
                if ($SettingBD->EnableOffs == 1 or $SettingBD->EnableOffs == '1'){
                    $EnableOffs = true;
                } else { $EnableOffs = false; }
                if ($SettingBD->operations == 1 or $SettingBD->operations == '1'){
                    $operations = true;
                } else { $operations = false; }
                $message = [
                    'total' => $info_total_and_SkipLoyaltyTotal['total'],
                    'SkipLoyaltyTotal' => $info_total_and_SkipLoyaltyTotal['SkipLoyaltyTotal'],
                    'availablePoints' => $this->AgentMCID($objectId, $Setting),
                    'points' => "0",
                    'phone' => $this->AgentMCPhone($objectId, $Setting),
                    'EnableOffs' => $EnableOffs,
                    'operations' => $operations,
                ];
            }
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
            ];

    }

    public  function CompletesOrder($accountId, $objectId){
        $Setting = new getSettingVendorController($accountId);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        try {
            $url = 'https://api.uds.app/partner/v2/goods-orders/'.$objectId.'/complete';
            $body = $Client->post($url, null);
            $StatusCode = "200";
            $message = "Заказ завершён";
            return [
                'StatusCode' => $StatusCode,
                'message' => $message,
            ];
        } catch (ClientException $exception){
            return [
                'StatusCode' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ];
        }
    }

    private function newPostOperations($accountId, $ClientUDS,  $externalCode, $agentId){
        $url = 'https://api.uds.app/partner/v2/operations/'.$externalCode;

        try {
            $body = $ClientUDS->get($url);
            $status = true;
            if ($body->points < 0) $points = $body->points * -1;
            else $points = $body->points ;
            $data = [
                'id'=> $body->id,
                'BonusPoint'=> $this->Calc($accountId, $ClientUDS, $body, $agentId),
                'points'=> $points,
                'state'=> "COMPLETED",
                'icon'=> '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Проведённая операция</span> </i>',
                'info'=> 'Operations',
            ];

        } catch (\Throwable $e) {
            $message = $e->getMessage();
            errorLog::create([
                'accountId' => $accountId,
                'ErrorMessage' => $message,
            ]);

            $status = false;
            $data = null;
        }
        return [
            'status' => $status,
            'data' => $data,
        ];
    }
    private function AgentMCID($objectId, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$objectId;
        $Clinet = new MsClient($Setting->TokenMoySklad);
        $bodyAgentHref = $Clinet->get($url)->agent->meta->href;
        $bodyMC = $Clinet->get($bodyAgentHref);
        //ПРОВЕРВИТЬ ВНЕШНИЙ КОД

        try {
            $url_UDS = 'https://api.uds.app/partner/v2/customers/'.$bodyMC->externalCode;
            $ClinetUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);
            $body = $ClinetUDS->get($url_UDS)->participant;
            return $body->points ;
        } catch (\Throwable $e) {
            return "Не известно";
        }


    }
    private function AgentMCPhone($objectId, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$objectId;
        $Clinet = new MsClient($Setting->TokenMoySklad);
        $bodyAgentHref = $Clinet->get($url)->agent->meta->href;
        $bodyMC = $Clinet->get($bodyAgentHref);
        //ПРОЕРВИТЬ ВНЕШНИЙ КОД
        return '+7 '.$bodyMC->phone;

    }
    private function TotalAndSkipLoyaltyTotal($objectId, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$objectId;
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
    private function Calc($accountId, $ClientUDS, $body, $agentId){
        $url = 'https://api.uds.app/partner/v2/operations/calc';
        $body = [
            'code' => null,
            'participant' => [
                'uid' => null,
                'phone' => '+7' . str_replace('+7','',str_replace(" ", '', $agentId['phone'])),
            ],
            'receipt' => [
                'total' => $body->total,
                'points' => ($body->points * -1),
                'skipLoyaltyTotal' => null,
            ],
        ];
        try {
            $postBody = $ClientUDS->post($url, $body)->purchase->cashBack;
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            errorLog::create([
                'accountId' => $accountId,
                'ErrorMessage' => $message,
            ]);
        }
        return $postBody;
    }


    public function operationsCalc(Request $request){

        $data = $request->validate([
            "accountId" => 'required|string',
            "user" => "required|string",
            "total" => "required|string",
            "SkipLoyaltyTotal" => "required|string",
            "points" => "required|string",
        ]);

        if ( strlen( str_replace(' ','',$data['user']) ) > 6) {
            $data['code'] = null;
            $data['phone'] = str_replace("+7", '', $data['user']);
            $data['phone'] = '+7' . str_replace(" ", '', $data['phone']);
        } else {
            $data['code'] = $data['user'];
            $data['phone'] = null;
        }

        if ($data['SkipLoyaltyTotal']  == '0') {
            $data['SkipLoyaltyTotal'] = null;
        }

        $Setting = new getSettingVendorController($data['accountId']);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $url = 'https://api.uds.app/partner/v2/operations/calc';
        $body = [
            'code' => $data['code'],
            'participant' => [
                'uid' => null,
                'phone' => $data['phone'],
            ],
            'receipt' => [
                'total' => $data['total'],
                'points' => $data['points'],
                'skipLoyaltyTotal' => $data['SkipLoyaltyTotal'],
            ],
        ];

        try {
            $postBody = $Client->post($url, $body)->purchase;
            return response()->json($postBody);
        } catch (\Throwable $e) {
            return response()->json(['Status' => $e->getCode(), 'Message' => $e->getMessage()]);
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

        try {
            $post = $Client->post($url, $body);

            $urlMC = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/' . $data['objectId'];
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
            $post = [
                'code' => 200,
                'id' => $post->id,
                'points' => $post->points,
                'total' => $post->total,
                'message' => 'The operation was successful',
            ];

        } catch ( \Throwable $e){
            $post = [
               'code' =>  $e->getCode(),
               'message' =>  $e->getMessage(),
            ];
        }

        return response()->json($post);
    }

    private function Positions($postUDS, $skipLoyaltyTotal, $OldBody, $Setting){
        $Positions = [];
        $ClientMCPositions = new ClientMC($OldBody->positions->meta->href, $Setting->TokenMoySklad);
        $OldPositions = $ClientMCPositions->requestGet()->rows;

        $sumMC = $OldBody->sum - $skipLoyaltyTotal;
        if ($sumMC > 0) $pointsPercent = ( $postUDS->points * -1 )  * 100 / ( $sumMC / 100 ) ;  else $pointsPercent = 0;
        foreach ($OldPositions as $item){
            //$price = $item->quantity * $item->price - ($item->quantity * $item->price * ($item->discount / 100));
            $Positions[] = [
                'id' => $item->id,
                'accountId' => $item->accountId,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount + $pointsPercent,
                'vat' => $item->vat,
                'vatEnabled' => $item->vatEnabled,
                'assortment' => $item->assortment,
                'shipped' => $item->shipped,
                'reserve' => $item->reserve,
            ];
        }
        return $Positions;
    }
    private function Attributes($postUDS, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes';
        $Client = new ClientMC($url, $Setting->TokenMoySklad);
        $metadata = $Client->requestGet()->rows;
        $Attributes = null;
        foreach ($metadata as $item) {
            if ($item->name == "Списание баллов (UDS)") {
                if (($postUDS->points * -1) > 0) {
                    $Attributes[] = [
                        'meta' => $item->meta,
                        'value' => true,
                    ];
                } else {
                    $Attributes[] = [
                        'meta' => $item->meta,
                        'value' => false,
                    ];
                }
            }
            if ($item->name == "Начисление баллов (UDS)") {
                if ($postUDS->cash > 0) {
                    $Attributes[] = [
                        'meta' => $item->meta,
                        'value' => true,
                    ];
                } else {
                    $Attributes[] = [
                        'meta' => $item->meta,
                        'value' => false,
                    ];
                }
            }
        }
        return $Attributes;
    }
}
