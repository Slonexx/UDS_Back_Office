<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Http\Controllers\Web\RewardController;
use App\Services\counterparty\widgetCounterparty;
use App\Services\Operation\OperationsCalc;
use App\Services\Operation\sendOperations;
use App\Services\Operation\WidgetInfo;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $Setting = new getSettingVendorController($accountId);
        $Client = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        try {
            $url = 'https://api.uds.app/partner/v2/goods-orders/' . $objectId . '/complete';
            $Client->post($url, null);
            $StatusCode = "200";
            $message = "Заказ завершён";
            return [
                'StatusCode' => $StatusCode,
                'message' => $message,
            ];
        } catch (BadResponseException $exception) {
            return [
                'StatusCode' => $exception->getCode(),
                'message' => $exception->getMessage(),
            ];
        }
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
            "user" => "required|string",
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
            "user_uid" => "required|string",
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









}
