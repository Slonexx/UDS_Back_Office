<?php

namespace App\Http\Controllers\BackEnd;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Models\webhookClintLog;
use App\Models\webhookOrderLog;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class postController extends Controller
{
    private AttributeHook $attributeHook;

    /**
     * @param AttributeHook $attributeHook
     */
    public function __construct(AttributeHook $attributeHook)
    {
        $this->attributeHook = $attributeHook;
    }


    public function postClint(Request $request, $accountId){
        $Setting = new getSettingVendorController($accountId);
        $TokenMC = $Setting->TokenMoySklad;
        $companyId = $Setting->companyId;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";
        $Clint = new ClientMC($url, $TokenMC);

        $participant = $request->participant;
        $email = $this->ClintNullable($request->email);
        $externalCode = $this->postClintId($TokenMC, $participant['id']);

        $body = [
            "name" => $request->displayName,
            "phone" => (string) $request->phone,
            "email" => $email,
            "externalCode" => (string) $externalCode,
        ];

        if ($externalCode != null) {
            $result = $Clint->requestPost($body);
            if (isset($result->errors)){
                webhookClintLog::create([
                    'accountId' => $accountId,
                    'message' => "error = ".$result->errors[0]->error."\n"."code = ".$result->errors[0]->code,
                    'companyId' => $companyId,
                ]);
            } else {
                $message = "Новый клиент = ". $request->displayName;
                webhookClintLog::create([
                    'accountId' => $accountId,
                    'message' => $message,
                    'companyId' => $companyId,
                ]);
            }
        }  $result = null;
    }

    public function ClintNullable($item){
        if ($item == null){
            return '';
        } else {
            return $item;
        }
    }

    public function postClintId($apiKei, $externalCode){
        $url = 'https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode='.$externalCode;
        $Client = new ClientMC($url, $apiKei);
        $body = $Client->requestGet()->rows;
        if (array_key_exists(1,$body)) return null;
        else return $externalCode;
    }





    public function postOrder(Request $request, $accountId){
        try {
            $Setting = new getSettingVendorController($accountId);
            $TokenMC = $Setting->TokenMoySklad;
            $companyId = $Setting->companyId;

            if ($Setting->creatDocument == "1"){
                $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder";
                $Clint = new ClientMC($url, $TokenMC);

                $BD = new BDController();
                $BD->createOrderID($accountId, $request->id, $companyId);
                //$BD->deleteOrderID($accountId, $request->id);

                try {
                    $organization = $this->metaOrganization($TokenMC, $Setting->Organization);
                    $organizationAccount = $this->metaOrganizationAccount($TokenMC, $Setting->PaymentAccount, $Setting->Organization);
                    $agent = $this->metaAgent($TokenMC, $request->customer['id']);
                    $state = $this->metaState($TokenMC, $Setting->NEW);
                    $store = $this->metaStore($TokenMC, $Setting->Store);
                    $salesChannel = $this->metaSalesChannel($TokenMC, $Setting->Saleschannel);
                    $attributes = $this->metaAttributes($TokenMC, $request->purchase);
                    $project = $this->metaProject($TokenMC, $Setting->Project);
                    $shipmentAddress = $this->ShipmentAddress($request->delivery);

                    $description = $request->delivery['userComment'];
                    if ($description == null)  $description = "";

                    $positions = $this->metaPositions($TokenMC, $request->items, $request->purchase, $request->delivery);
                    $externalCode = $this->CheckExternalCode($TokenMC, $request->id);
                } catch (ClientException $exception) {
                    $message = $exception->getMessage();
                    webhookOrderLog::create([
                        'accountId' => $accountId,
                        'message' => $message,
                        'companyId' => $companyId,
                    ]);
                }

                if ($organizationAccount != null)
                    $body = [
                        "organization" => $organization,
                        "organizationAccount" => $organizationAccount,
                        "agent" => $agent,//Создавать АГЕНТА НАДО
                        "state" => $state,
                        "store" => $store,
                        "salesChannel" => $salesChannel,
                        "project" => $project,
                        "shipmentAddress" => $shipmentAddress,
                        "description" => $description,

                        "attributes" => $attributes,
                        "positions" => $positions,
                        "externalCode" => $externalCode,
                    ];
                else
                    $body = [
                        "organization" => $organization,
                        "agent" => $agent,//Создавать АГЕНТА НАДО
                        "state" => $state,
                        "store" => $store,
                        "salesChannel" => $salesChannel,
                        "project" => $project,
                        "shipmentAddress" => $shipmentAddress,
                        "description" => $description,

                        "attributes" => $attributes,
                        "positions" => $positions,
                        "externalCode" => $externalCode,
                    ];

                if ($externalCode != null) {
                    $result = $Clint->requestPost($body);
                    if (isset($result->errors)){
                        webhookOrderLog::create([
                            'accountId' => $accountId,
                            'message' => "error = ".$result->errors[0]->error."\n"."code = ".$result->errors[0]->code,
                            'companyId' => $companyId,
                        ]);
                    } else {
                        $message = "Покупатель = ".$request->delivery["receiverName"] . "\n" . "Заказал = ". count($request->items). " Товар(а) "
                            . "\n" . "Сумма = " . $request->total;
                        webhookOrderLog::create([
                            'accountId' => $accountId,
                            'message' => $message,
                            'companyId' => $companyId,
                        ]);
                    }
                }  $result = null;

            }
        } catch (ClientException $exception){
            $message = $exception->getMessage();
            webhookOrderLog::create([
                'accountId' => $accountId,
                'message' => $message,
                'companyId' => $companyId,
            ]);
        }

    }


    public function metaOrganization($apiKey, $Organization){
        $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$Organization;
        $Clint = new ClientMC($url_organization, $apiKey);
        $Body = $Clint->requestGet()->meta;
        $href = $Body->href;
        $type = $Body->type;
        $mediaType = $Body->mediaType;
        return [
           'meta' => [
               'href'=> $href,
               'type'=> $type,
               'mediaType'=> $mediaType,
           ]
        ];
    }

    public function metaOrganizationAccount($apiKey, $PaymentAccount, $Organization){

        if ($PaymentAccount == null) return null;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/organization/".$Organization."/accounts";
        $Clint = new ClientMC($url, $apiKey);
        $Body = $Clint->requestGet()->rows;
        foreach ($Body as $item){
            if ($item->accountNumber == $PaymentAccount){
                $href = $item->meta->href;
                $type = $item->meta->type;
                $mediaType = $item->meta->mediaType;
                break;
            } else {
                $href = null;
            }
        }

        if ($href == null) return null;

        return [
           'meta' => [
               'href'=> $href,
               'type'=> $type,
               'mediaType'=> $mediaType,
           ]
        ];
    }

    public function metaAgent($apiKey, $agent){
        $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty?filter=externalCode~".$agent;
        $Clint = new ClientMC($url_organization, $apiKey);
        $Body = $Clint->requestGet()->rows[0]->meta; //Может не быть

        $href = $Body->href;
        $type = $Body->type;
        $mediaType = $Body->mediaType;

        return [
            'meta' => [
                'href'=> $href,
                'type'=> $type,
                'mediaType'=> $mediaType,
            ]
        ];
    }

    public function metaState($apiKey, $Status){

        if ($Status == null){
            return null;
        }
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata/";
        $Clint = new ClientMC($url, $apiKey);
        $Body = $Clint->requestGet()->states;
        foreach ($Body as $item){
            if ($item->name == $Status) {
                $href = $item->meta->href;
                $type = $item->meta->type;
                $mediaType = $item->meta->mediaType;
                break;
            } else $href = null;
        }
        if ($href == null) return null;
        return [
            'meta' => [
                'href'=> $href,
                'type'=> $type,
                'mediaType'=> $mediaType,
            ]
        ];
    }

    public function metaStore($apiKey, $StoreName){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/store/";
        $Clint = new ClientMC($url, $apiKey);
        $body = $Clint->requestGet()->rows;
        foreach ($body as $item){
            if ($item->name == $StoreName){
                $href = $item->meta->href;
                $type = $item->meta->type;
                $mediaType = $item->meta->mediaType;
                break;
            } else $href = null;
        }
        if ($href == null) return null;
        return [
            'meta' => [
                'href'=> $href,
                'type'=> $type,
                'mediaType'=> $mediaType,
            ]
        ];
    }

    public function metaSalesChannel($apiKey, $salesChannelName){

        if ($salesChannelName == null) return null;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel?search=".$salesChannelName;
        $Clint = new ClientMC($url, $apiKey);
        $Body = $Clint->requestGet()->rows[0]->meta;
        $href = $Body->href;
        $type = $Body->type;
        $mediaType = $Body->mediaType;
        return [
            'meta' => [
                'href'=> $href,
                'type'=> $type,
                'mediaType'=> $mediaType,
            ]
        ];
    }

    public function metaProject($apiKey, $Project){
        if ($Project == null) return null;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/project?search=".$Project;
        $Clint = new ClientMC($url, $apiKey);
        $Body = $Clint->requestGet()->rows[0]->meta;
        $href = $Body->href;
        $type = $Body->type;
        $mediaType = $Body->mediaType;
        return [
            'meta' => [
                'href'=> $href,
                'type'=> $type,
                'mediaType'=> $mediaType,
            ]
        ];
    }

    public function metaAttributes($apiKey, $purchase){
        if ($purchase['points'] > 0 )
            $DeductionOfPoints = [
                'meta' => $this->attributeHook->getOrderAttribute('Списание баллов (UDS)', $apiKey),
                'value' => true,
        ];
        else  $DeductionOfPoints = [
            'meta' => $this->attributeHook->getOrderAttribute('Списание баллов (UDS)', $apiKey),
            'value' => false,
        ];

        if ($purchase['cashBack'] > 0 )
            $AccrualOfPoints = [
                'meta' => $this->attributeHook->getOrderAttribute('Начисление баллов (UDS)', $apiKey),
                'value' => true,
            ];
        else  $AccrualOfPoints = [
            'meta' => $this->attributeHook->getOrderAttribute('Начисление баллов (UDS)', $apiKey),
            'value' => false,
        ];

        if ($purchase['certificatePoints'] > 0 )
            $UsingCertificate = [
                'meta' => $this->attributeHook->getOrderAttribute('Использование сертификата (UDS)', $apiKey),
                'value' => true,
            ];
        else  $UsingCertificate = [
            'meta' => $this->attributeHook->getOrderAttribute('Использование сертификата (UDS)', $apiKey),
            'value' => false,
        ];

        $array = [$DeductionOfPoints, $AccrualOfPoints, $UsingCertificate];

        return $array;

    }

    public function metaPositions($apiKey, $UDSitem, $purchase, $delivery){
        $urlMeta = "https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes";
        $Client = new ClientMC($urlMeta, $apiKey);
        $BodyMeta = $Client->requestGet()->rows;
        foreach ($BodyMeta as $BodyMeta_item){
            if ($BodyMeta_item->name == 'id (UDS)'){ $BodyMeta = $BodyMeta_item->meta->href;
                break;
            }
        }

        $total = $purchase["total"] - $purchase["skipLoyaltyTotal"];
        if ($purchase["points"]+$purchase["certificatePoints"] > 0) $pointsPercent = ($purchase["certificatePoints"] + $purchase["points"])  * 100 / $total;
        else $pointsPercent = 0;

        $Result = [];
        foreach ($UDSitem as $id=>$item){
            $urlProduct = 'https://online.moysklad.ru/api/remap/1.2/entity/product?filter='.$BodyMeta.'='.$item['id'];
            $Client = new ClientMC($urlProduct, $apiKey);
            $body = $Client->requestGet()->rows;
            //dd($body);
            $bodyIndex = 0;
            if  (isset($body[1])) {
                foreach ($body as $bodyCheckID=>$bodyCheckItem){
                    //$tmp = $item['variantName']."(".$item['name'].")";
                    if ($item['variantName']."(".$item['name'].")" == $bodyCheckItem->name ) {
                        $bodyIndex = $bodyCheckID;
                        break;
                    } else $bodyIndex = 0 ;
                }
            } else $bodyIndex = 0;
            $body = $body[$bodyIndex];
            foreach ($body->attributes as $attributesItem){
                if ('Не применять бонусную программу (UDS)' == $attributesItem->name){
                    if ($attributesItem->value == true) $discount = 0;
                    else $discount = $pointsPercent;
                    break;
                } else $discount = $pointsPercent;
            }

            $assortment = [ 'meta' => [
                     'href' => $body->meta->href,
                     'type' => $body->meta->type,
                     'mediaType' => $body->meta->mediaType,
                ]
            ];
            $ArrayItem = [
                'quantity' => $item['qty'],
                'price' => $item['price']*100,
                'assortment' => $assortment,
                'discount' => $discount,
                'reserve' => $item['qty'],
            ];
            $Result[] = $ArrayItem;
        }

        if ($delivery['deliveryCase'] != null) {
            $deliveryCase = $this->delivery($apiKey, $delivery['deliveryCase']);
            $ArrayItem = [
                'quantity' => 1,
                'price' => $deliveryCase['price']*100,
                'assortment' => $deliveryCase['assortment'],
            ];

            $Result[] = $ArrayItem;
        }


       return $Result;
    }

    public function ShipmentAddress($delivery){
        $DELIVERY = "";
        $PICKUP = "";
        if ($delivery["branch"]) { $PICKUP = "(САМОВЫВОЗ) ".$delivery["branch"]["displayName"];
            return $PICKUP;
        }
        if ($delivery["address"]) {
            $DELIVERY = $delivery["address"];
            $deliveryCase = $delivery["deliveryCase"];
            return $DELIVERY;
        }
        return null;
    }

    public function CheckExternalCode($apiKey, $externalCode){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder?filter=externalCode~".$externalCode;
        $Clint = new ClientMC($url, $apiKey);
        $body = $Clint->requestGet()->rows;
        if (!$body) return (string) $externalCode;
        else return null;
    }

    public function delivery($apiKey, $deliveryCase){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/assortment?filter=externalCode=Доставка(UDS)";
        $Client = new ClientMC($url, $apiKey);
        $body = $Client->requestGet()->rows;

        if (array_key_exists(0, $body)) $body = $body[0];
        else {
            $urlService = "https://online.moysklad.ru/api/remap/1.2/entity/service";
            $ClientService = new ClientMC($urlService, $apiKey);
            $bodyService = [
                'name' => 'Доставка (UDS)',
                'externalCode' => 'Доставка(UDS)'
            ];
            $body = $ClientService->requestPost($bodyService);
        }

        return [
            'assortment' => [
                'meta' => [
                    'href' => $body->meta->href,
                    'type' => $body->meta->type,
                    'mediaType' => $body->meta->mediaType,
                ]
            ],
            'price' => $deliveryCase['value'],
        ];
    }
}
