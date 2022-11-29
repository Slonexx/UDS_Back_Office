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
    public function CounterpartyObject(Request $request, $accountId, $entity, $objectId): \Illuminate\Http\JsonResponse
    {

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


    public function CustomerOrderEditObject($accountId, $entity, $objectId): array
    {

        $UDSURL = "https://api.uds.app/partner/v2/goods-orders/";

        $SettingBD = new getSetting();
        $SettingBD = $SettingBD->getSendSettingOperations($accountId);
        $Setting = new getSettingVendorController($accountId);

        $urlCounterparty = "https://online.moysklad.ru/api/remap/1.2/entity/$entity/$objectId";
        $BodyMC = new ClientMC($urlCounterparty, $Setting->TokenMoySklad);
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
            $body = $Clint->get($UDSURL.$externalCode);
            $StatusCode = "200";
            $state = $body->state;
            $icon = "";
            if ($state == "NEW") $icon = '<i class="fa-solid fa-circle-exclamation text-primary">  <span class="text-dark">НОВЫЙ</span> </i>';
            if ($state == "COMPLETED") $icon = '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Завершённый</span> </i>';
            if ($state == "DELETED") $icon = '<i class="fa-solid fa-circle-xmark text-danger"> <span class="text-dark">Отменённый</span> </i>';

            $message = [
                'id'=> $body->id,
                'BonusPoint'=>  $body->purchase->cashBack,
                'points'=> $body->purchase->points,
                'state'=> $state,
                'icon'=> $icon,
                'info'=> 'Order',
            ];
        } catch (ClientException $exception) {
            $data = $this->newPostOperations($accountId, $Clint, $externalCode, $agentId);
            if ($data['status']) { $StatusCode = "200"; $message = $data['data'];
            } else {
                $StatusCode = "404";
                $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($objectId, $Setting);
                $availablePoints = $this->AgentMCID($href,  $Setting);
                $phone = $this->AgentMCPhone($href, $Setting);
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
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
            ];

    }

    public  function CompletesOrder($accountId, $objectId): array
    {
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

    public function newPostOperations($accountId, $ClientUDS,  $externalCode, $agentId){
        $url = 'https://api.uds.app/partner/v2/operations/'.$externalCode;
        try {
            $body = $ClientUDS->get($url);
            if ($body->points < 0) $points = $body->points * -1; else $points = $body->points ;
            $status = true;
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
    public function AgentMCID($href, $Setting){
        $Clinet = new MsClient($Setting->TokenMoySklad);
        $bodyAgentHref = $href;
        $bodyMC = $Clinet->get($bodyAgentHref);

        try {
            $url_UDS = 'https://api.uds.app/partner/v2/customers/'.$bodyMC->externalCode;
            $ClinetUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);
            $body = $ClinetUDS->get($url_UDS)->participant;
            return $body->points ;
        } catch (\Throwable $e) {
            $result = 0;
            if (property_exists($bodyMC, 'phone')) {
                $phone = urlencode ((string) '+'. $this->phone_number($bodyMC->phone));
                $url = 'https://api.uds.app/partner/v2/customers/find?phone='.$phone;
                try {
                    $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
                    $Body = $Client->get($url)->user;
                    $result = $Body->participant->points;
                } catch (\Throwable $e) { $result = 0; }
                return $result;
            }

            return $result;
        }


    }
    public function AgentMCPhone($href, $Setting): string
    {
        $Clinet = new MsClient($Setting->TokenMoySklad);
        $bodyAgentHref = $href;
        $bodyMC = $Clinet->get($bodyAgentHref);
        $phone = preg_replace('/[^0-9]/', '', $bodyMC->phone);
        if($phone[0]==8)$phone[0] = 7;
        if ( strlen($phone) == 10 ) $phone = '7'.$phone;
        return '+'.$phone;

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
            $body = $Clinet->get($url_item);

            $BonusProgramm = false;
            if (property_exists($body, 'attributes')){
                foreach ($body as $body_item){
                    if ('Не применять бонусную программу (UDS)' == $body_item->name){
                        $BonusProgramm = $body_item->value;
                        break;
                    }
                }
            } else  $BonusProgramm = false;

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
    public function Calc($accountId, $ClientUDS, $body, $agentId){
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

    public function customers(Request $request){
        $data = $request->validate([
            "accountId" => 'required|string',
            "code" => 'required|string',
        ]);

        $Setting = new getSettingVendorController($data['accountId']);
        $url = 'https://api.uds.app/partner/v2/customers/find?code='.$data['code'];
        $result = null;
        try {
            $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
            $Body = $Client->get($url)->user;
            $result = [
                'id' => $Body->participant->id,
                'availablePoints' => $Body->participant->points,
                'displayName' => $Body->displayName,
            ];
        } catch (\Throwable $exception) {
            $result =[
                'id' => 0,
                'availablePoints' => 0,
                'displayName' => 0,
            ];
        }

        return response()->json($result);

    }


    public function Positions($postUDS, $skipLoyaltyTotal, $OldBody, $Setting){
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
    public function Attributes($postUDS, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/attributes';
        $Client = new ClientMC($url, $Setting->TokenMoySklad);
        $metadata = $Client->requestGet()->rows;
        $Attributes = null;
        foreach ($metadata as $item) {
            if ($item->name == "Списание баллов (UDS)") {
                if (($postUDS->points * -1) > 0) {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => true,
                    ];
                } else {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => false,
                    ];
                }
            }
            if ($item->name == "Начисление баллов (UDS)") {
                if ($postUDS->cash > 0) {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => true,
                    ];
                } else {
                    $Attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => false,
                    ];
                }
            }
        }
        return $Attributes;
    }
    public function createDemands($Setting, $SettingBD, $OldBody, $externalCode){
        if ($SettingBD->operationsDocument == 0 or $SettingBD->operationsDocument == null) {

        } else {
            $client = new MsClient($Setting->TokenMoySklad);
            $attributes = null;
            $attributes_value = null;
            $Store = $Setting->Store;
            $bodyStore = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/store?filter=name='.$Store)->rows;
            $Store = $bodyStore[0]->id;
            $bodyAttributes = $client->get("https://online.moysklad.ru/api/remap/1.2/entity/demand/metadata/attributes/")->rows;
            foreach ($OldBody->attributes as $item) {
                if ($item->name == "Начисление баллов (UDS)") {
                    $attributes_value[$item->name] = [
                        'value' => $item->value
                    ];
                }
                if ($item->name == "Списание баллов (UDS)") {
                    $attributes_value[$item->name] = [
                        'value' => $item->value
                    ];
                }
            }
            foreach ($bodyAttributes as $item) {
                if ($item->name == "Начисление баллов (UDS)") {
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $attributes_value[$item->name]['value']
                    ];
                }
                if ($item->name == "Списание баллов (UDS)") {
                    $attributes[] = [
                        'meta' => [
                            'href' => $item->meta->href,
                            'type' => $item->meta->type,
                            'mediaType' => $item->meta->mediaType,
                        ],
                        'value' => $attributes_value[$item->name]['value']
                    ];
                }
            }
            $href_positions = $OldBody->positions->meta->href;
            $bodyPositions = $client->get($href_positions)->rows;
            foreach ($bodyPositions as $id=>$item) {
                $positions[$id] = [
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'discount' => $item->discount,
                    'vat' => $item->vat,
                    'assortment' => ['meta'=> [
                        'href' => $item->assortment->meta->href,
                        'type' => $item->assortment->meta->type,
                        'mediaType' => $item->assortment->meta->mediaType,
                    ] ],
                ];
            }
            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/demand';
            $body = [
                'organization' => [  'meta' => [
                    'href' => $OldBody->organization->meta->href,
                    'type' => $OldBody->organization->meta->type,
                    'mediaType' => $OldBody->organization->meta->mediaType,
                ] ],
                'agent' => [ 'meta'=> [
                    'href' => $OldBody->agent->meta->href,
                    'type' => $OldBody->agent->meta->type,
                    'mediaType' => $OldBody->agent->meta->mediaType,
                ] ],
                'store' => [ 'meta'=> [
                    'href' => 'https://online.moysklad.ru/api/remap/1.2/entity/store/'.$Store,
                    'type' => 'store',
                    'mediaType' => 'application/json',
                ] ],
                'externalCode' => $externalCode,
                'attributes' => $attributes,
                'positions' => $positions,
                'customerOrder' => [
                    'meta'=> [
                        'href' => $OldBody->meta->href,
                        'metadataHref' => $OldBody->meta->metadataHref,
                        'type' => $OldBody->meta->type,
                        'mediaType' => $OldBody->meta->mediaType,
                        'uuidHref' => $OldBody->meta->uuidHref,
                    ] ],
            ];
            try {
                $postBodyCreateDemand = $client->post($url, $body);
                if ($SettingBD->operationsDocument == '2' or $SettingBD->operationsDocument == 2) {
                    $body = [
                        'demands' => [  0 => [ 'meta' => [
                            'href' => $postBodyCreateDemand->meta->href,
                            'metadataHref' => $postBodyCreateDemand->meta->metadataHref,
                            'type' => $postBodyCreateDemand->meta->type,
                            'mediaType' => $postBodyCreateDemand->meta->mediaType,
                        ] ] ] ];

                    $urlFacture = 'https://online.moysklad.ru/api/remap/1.2/entity/factureout';
                    $client = new MsClient($Setting->TokenMoySklad);
                    $postBodyCreateFactureout = $client->post($urlFacture, $body);
                }
            } catch (\Throwable $e) {
                dd($e);
            }
        }
    }
    public function createPaymentDocument($Setting, $SettingBD, $OldBody ){
        if ($SettingBD->operationsPaymentDocument == 0 or $SettingBD->operationsPaymentDocument == null) {

        } else {  $client = new MsClient($Setting->TokenMoySklad);
            if ($SettingBD->operationsPaymentDocument == 1 or $SettingBD->operationsPaymentDocument == "1") {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/cashin';

                $body = [
                    'organization' => [  'meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ] ],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => 0
                        ], ]
                ];
                $postBodyCreateCashin = $client->post($url, $body);
            }
            if ($SettingBD->operationsPaymentDocument == 2) {
                $url = 'https://online.moysklad.ru/api/remap/1.2/entity/paymentin';

                $body = [
                    'organization' => [  'meta' => [
                        'href' => $OldBody->organization->meta->href,
                        'type' => $OldBody->organization->meta->type,
                        'mediaType' => $OldBody->organization->meta->mediaType,
                    ] ],
                    'agent' => [ 'meta'=> [
                        'href' => $OldBody->agent->meta->href,
                        'type' => $OldBody->agent->meta->type,
                        'mediaType' => $OldBody->agent->meta->mediaType,
                    ] ],
                    'sum' => $OldBody->sum,
                    'operations' => [
                        0 => [
                            'meta'=> [
                                'href' => $OldBody->meta->href,
                                'metadataHref' => $OldBody->meta->metadataHref,
                                'type' => $OldBody->meta->type,
                                'mediaType' => $OldBody->meta->mediaType,
                                'uuidHref' => $OldBody->meta->uuidHref,
                            ],
                            'linkedSum' => 0
                        ], ]
                ];
                $postBodyCreatePaymentin = $client->post($url, $body);
            }
        }
    }

    public function phone_number($phone){
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if($phone[0]==8)$phone[0] = 7;
        return $phone;
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
            $data['phone'] = $data['user'];
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
        $this->createDemands($Setting, $SettingBD, $putBody, (string) $post->id);
        $this->createPaymentDocument($Setting, $SettingBD, $putBody);
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
}
