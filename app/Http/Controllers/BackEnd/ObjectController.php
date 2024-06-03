<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Http\Controllers\Web\RewardController;
use App\Models\orderSettingModel;
use App\Services\counterparty\widgetCounterparty;
use App\Services\Operation\OperationsCalc;
use App\Services\Operation\sendOperations;
use App\Services\Operation\WidgetInfo;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheSeer\Tokenizer\Exception;

class ObjectController extends Controller
{

    public function CounterpartyObject($accountId, $objectId): JsonResponse
    {
        return ((new widgetCounterparty())->getInformation($accountId, $objectId));
    }

    public function CustomerOrderEditObject($accountId, $entity, $objectId): array
    {
        $object = new WidgetInfo();
        return $object->getInformation($accountId, $entity, $objectId);
    }


    public function CompletesOrder($accountId, $objectId): array
    {
        $StatusCode = "200";
        $message = "Заказ завершён";

        $Setting = new getSettingVendorController($accountId);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $msClient = new MsClient($Setting->TokenMoySklad);

        try {
            /* $url = 'https://api.uds.app/partner/v2/goods-orders/' . $objectId . '/complete';
           $Client->post($url, null);*/
        } catch (BadResponseException $e) {
            return [
                'StatusCode' => $e->getCode(),
                'message' => $e->getMessage(),
            ];
        }


        try {
            $Order = $msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/customerorder?filter=externalCode=' . $objectId)->rows['0'];
        } catch (BadResponseException|\Throwable $e) {
            return [ 'status' => false,  'message' =>  'Заказ завершен, но '.$e->getMessage() ];
        }


        $body = $this->CreateDemand($Setting, $msClient, $Order);
        if ($body['status'] and (!property_exists($Order, 'demand'))) {
            try {
                $msClient->post('https://api.moysklad.ru/api/remap/1.2/entity/demand', $body['data']);
            } catch (BadResponseException){
                return [ 'StatusCode' => $StatusCode, 'message' => 'Заказ завершен, не удалось создать отгрузку' ];
            }
        }

        if ((!property_exists($Order, 'payments'))) $this->CreatePaymentDocument($accountId, $msClient, $Order);



        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
        ];
    }

    public function customers(Request $request): JsonResponse
    {
        $data = $request->validate([
            "accountId" => 'required|string',
            "code" => 'required|string',
        ]);

        $Setting = new getSettingVendorController($data['accountId']);
        $url = 'https://api.uds.app/partner/v2/customers/find?code=' . $data['code'];
        try {
            $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
            $Body = $Client->get($url)->user;
            $result = [
                'id' => $Body->participant->id,
                'availablePoints' => $Body->participant->points,
                'displayName' => $Body->displayName,
            ];
        } catch (BadResponseException) {
            $result = [
                'id' => 0,
                'availablePoints' => 0,
                'displayName' => 0,
            ];
        }

        return response()->json($result);

    }

    public function operationsCalc(Request $request): JsonResponse
    {
        $data = $request->validate([
            "accountId" => 'required|string',
            "user" => "nullable|string",
            "total" => "required|string",
            "SkipLoyaltyTotal" => "required|string",
            "points" => "required|string",
            "entity_type" => "required|string",
            "object_Id" => "required|string",
        ]);
        return response()->json((new OperationsCalc())->initiation($data));
    }

    public function operations(Request $request): JsonResponse
    {

        $data = $request->validate([
            "accountId" => 'required|string',
            "objectId" => 'required|string',
            "entity" => 'required|string',
            "user" => "required|string",
            "user_uid" => "nullable|string",
            "cashier_id" => "required|string",
            "cashier_name" => "required|string",
            "receipt_total" => "required|string",
            "receipt_cash" => "required|string",
            "receipt_points" => "required|string",
            "receipt_skipLoyaltyTotal" => "required|string",
            "cashBack" => "required|string",
        ]);

        return response()->json((new sendOperations())->initiation($data));
    }

    private function CreateDemand(getSettingVendorController $Setting, MsClient $msClient, $Order)
    {
        $body = [];
        $orderSetting = orderSettingModel::where('accountId', $Setting->accountId)->get()->first();
        if ($orderSetting != null) $orderSetting = $orderSetting->toArray();
        else return ['status' => false, 'message' => 'Заказ завершён", Отсутствуют настройки создание отгрузки!'];


        $body['organization'] = $Order->organization;
        if (property_exists($Order, 'organizationAccount')) $body['organizationAccount'] = $Order->organizationAccount;

        try {
            $Store = $msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store?search=' . $Setting->Store)->rows['0'];
        } catch (BadResponseException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }

        $body['agent'] = $Order->agent;
        $body['store'] = (object)['meta' => $Store->meta];
        if (property_exists($Order, 'shipmentAddress')) $body['shipmentAddress'] = $Order->shipmentAddress;
        if (property_exists($Order, 'salesChannel')) $body['salesChannel'] = $Order->salesChannel;
        if (property_exists($Order, 'project')) $body['project'] = $Order->project;


        try {
            $pos = $msClient->get($Order->positions->meta->href)->rows;
        } catch (BadResponseException $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }


        $positions = [];

        foreach ($pos as $item) {
            $positions[] = [
                'quantity' => $item->quantity,
                'price' => $item->price,
                'discount' => $item->discount,
                'vat' => $item->vat,
                'assortment' => $item->assortment,
                'reserve' => 0,
            ];
        }
        $body['positions'] = $positions;

        $body['externalCode'] = $Order->externalCode;
        $body['customerOrder'] = (object)['meta' => $Order->meta];

        return [
            'status' => true,
            'data' => $body
        ];
    }

    private function CreatePaymentDocument($accountId, MsClient $msClient, mixed $Order)
    {
        $SettingBD = (new getSetting())->getSendSettingOperations($accountId);
        if ($SettingBD->operationsPaymentDocument == 0 || $SettingBD->operationsPaymentDocument == null) return;

        $url = '';
        $body = [
            'organization' => ['meta' => [
                'href' => $Order->organization->meta->href,
                'type' => $Order->organization->meta->type,
            ]],
            'agent' => ['meta' => [
                'href' => $Order->agent->meta->href,
                'type' => $Order->agent->meta->type,
            ]],
            'sum' => $Order->sum,
            'operations' => [
                0 => [
                    'meta' => [
                        'href' => $Order->meta->href,
                        'type' => $Order->meta->type,
                    ],
                    'linkedSum' => $Order->sum,
                ],
            ],
        ];

        if ($SettingBD->operationsPaymentDocument == 1) $url = 'https://api.moysklad.ru/api/remap/1.2/entity/cashin';
        if ($SettingBD->operationsPaymentDocument == 2) $url = 'https://api.moysklad.ru/api/remap/1.2/entity/paymentin';

        if ($url != '') $msClient->post($url, $body);
    }


}
