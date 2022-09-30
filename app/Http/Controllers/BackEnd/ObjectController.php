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
            $info_total_and_SkipLoyaltyTotal = $this->TotalAndSkipLoyaltyTotal($objectId, $Setting);
            //dd($info_total_and_SkipLoyaltyTotal['total']);
            $message = [
                'total' => $info_total_and_SkipLoyaltyTotal['total'],
                'SkipLoyaltyTotal' => $info_total_and_SkipLoyaltyTotal['SkipLoyaltyTotal'],
                'points' => $this->AgentMCID($objectId, $Setting),
                'phone' => $this->AgentMCPhone($objectId, $Setting),

            ];
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
    private function AgentMCID($objectId, $Setting){
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


    public function operationsCalc(Request $request){

        $data = $request->validate([
            "accountId" => 'required|string',
            "user" => "required|string",
            "total" => "required|string",
            "SkipLoyaltyTotal" => "required|string",
            "points" => "required|string",
        ]);

        if ((int) str_replace(' ','',$data['user']) > 999999) {
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
        $postBody = $Client->post($url, $body)->purchase;
        return response()->json($postBody);
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

        if ((int) str_replace(' ','',$data['user']) > 999999) {
            $data['code'] = null;
            $data['phone'] = $data['user'];
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
                'cash' => $data['receipt_cash'],
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

            $OldBody->externalCode = $post->id;
            $postBody = $ClientMC->requestPut([
                'externalCode'=>(string) $post->id,
                'positions'=> $setPositions,
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
        if ($sumMC > 0) $pointsPercent = $postUDS->points  * 100 / $sumMC ;  else $pointsPercent = 0;

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

}
