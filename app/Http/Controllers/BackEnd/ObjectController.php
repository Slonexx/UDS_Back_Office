<?php

namespace App\Http\Controllers\BackEnd;

use App\Components\MsClient;
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
            $cashBack = $purchase->cashBack;
            $points = $purchase->points;
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
                'BonusPoint'=>  $cashBack,
                'points'=> $points,
                'state'=> $state,
                'icon'=> $icon,
            ];
        } catch (ClientException $exception) {
            $StatusCode = "404";
            $message = [
                'points' => $this->AgentMCID($objectId, $Setting),
                'message' => "В UDS Заказ не найден",
            ];
        }

        return [
            'StatusCode' => $StatusCode,
            'message' => $message,
            ];

    }

    public function CompletesOrder($accountId, $objectId){
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

    public function AgentMCID($objectId, $Setting){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/customerorder/'.$objectId;
        $Clinet = new MsClient($Setting->TokenMoySklad);
        $bodyAgentHref = $Clinet->get($url)->agent->meta->href;
        $bodyMC = $Clinet->get($bodyAgentHref);
        //ПРОЕРВИТЬ ВНЕШНИЙ КОД
        $url_UDS = 'https://api.uds.app/partner/v2/customers/'.$bodyMC->externalCode;
        $ClinetUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $body = $ClinetUDS->get($url_UDS)->participant;
        return $body->points ;

    }

}
