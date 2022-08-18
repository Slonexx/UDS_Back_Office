<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
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
        $externalCode = $BodyMC->requestGet()->externalCode;
        $Clint = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        try {
            $body = $Clint->get($UDSURL.$externalCode);
            $purchase = $body->purchase;
            $StatusCode = "200";
            $id = $body->id;
            $state = $body->state;
            $icon = "";
            if ($state == "NEW")
                $icon = '<i class="fa-solid fa-circle-exclamation text-primary">  <span class="text-dark">НОВЫЙ</span> </i>';

            if ($state == "COMPLETED")
                $icon = '<i class="fa-solid fa-circle-check text-success"> <span class="text-dark">Завершённый</span> </i>';

            if ($state == "DELETED")
                $icon = '<i class="fa-solid fa-circle-xmark text-danger"> <span class="text-dark">Отменённый</span> </i>';

            $message = [
                'id'=> $id,
                'state'=> $state,
                'icon'=> $icon,
            ];
            $body = $Clint->get($UDSURL.$externalCode);
            $purchase = $body->purchase;
            return [
                'StatusCode' => $StatusCode,
                'message' => $message,
                'purchase' => $purchase,
            ];
        } catch (ClientException $exception) {
            $StatusCode = "404";
            $message = "В UDS Заказ не найден";
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
            ];

    }
}
