<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;
use App\Http\Controllers\BackEnd\postController;
use App\Services\MetaServices\MetaHook\AttributeHook;

class DocumentService
{

    private AttributeHook $attributeHook;

    /**
     * @param AttributeHook $attributeHook
     */
    public function __construct(AttributeHook $attributeHook)
    {
        $this->attributeHook = $attributeHook;
    }


    public function initDocuments(
        $items,$purchase,$delivery,$paymentOption,
        $demandOption,$formattedOrder,$apiKey
    )
    {

        $sum = $formattedOrder->sum;
        $metaOrder = $formattedOrder->meta;

        if($paymentOption > 0){
            $this->createPayInDocument($apiKey,$metaOrder,$paymentOption,$formattedOrder,$sum);
        }

        if($demandOption > 0){
            $metaDemand = $this->createDenamd($apiKey,$metaOrder,$formattedOrder,$items,$purchase,$delivery);
            if($demandOption == 2){
                $this->createFactureout($apiKey,$metaDemand,$formattedOrder);
            }
        }

    }

    private function createFactureout($apiKey,$metaDemand, $formattedOrder)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/factureout";
        $client = new MsClient($apiKey);
        $docBody = [
            "demands" => [
                0 => [
                    "meta" => $metaDemand,
                ],
            ],
        ];

        foreach ($formattedOrder->attributes as $attribute){
            $docBody["attributes"][] = [
                "meta" => $this->attributeHook->getFactureOutAttribute($attribute->name,$apiKey),
                "value" => $attribute->value,
            ];
        }

        $client->post($uri,$docBody);
    }

    private function createPayInDocument($apiKey,$meta,$isPayment,$formattedOrder,$sum)
    {
        $uri = null;
        if ($isPayment == 2) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/paymentin";
        } elseif($isPayment == 1) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/cashin";
        }


        //dd($metaOrder);

        $client = new MsClient($apiKey);
        $docBody = [
            "agent" => $formattedOrder->agent,
            "organization" => $formattedOrder->organization,
            "rate" => $formattedOrder->rate,
            "sum" => $sum,
            "operations" => [
                0=> [
                    "meta" => $meta,
                ],
            ],
        ];

        foreach ($formattedOrder->attributes as $attribute){
            if ($isPayment == 1){
                $docBody["attributes"][] = [
                    "meta" => $this->attributeHook->getCashInAttribute($attribute->name,$apiKey),
                    "value" => $attribute->value,
                ];
            }elseif ($isPayment ==2){
                $docBody["attributes"][] = [
                    "meta" => $this->attributeHook->getPaymentInAttribute($attribute->name,$apiKey),
                    "value" => $attribute->value,
                ];
            }
        }

        //dd($docBody);

        if(property_exists($formattedOrder,"salesChannel")){
            $docBody["salesChannel"] = $formattedOrder->salesChannel;
        }

        if(property_exists($formattedOrder,"project")){
            $docBody["project"] = $formattedOrder->project;
        }

        if(property_exists($formattedOrder,"organizationAccount")){
            $docBody["organizationAccount"] = $formattedOrder->organizationAccount;
        }
        $client->post($uri,$docBody);
    }

    private function createDenamd($apiKey, $meta, $formattedOrder,$entries,$purchase,$delivery)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/demand";
        $client = new MsClient($apiKey);
        $docBodyDemand = [
            "agent" => $formattedOrder->agent,
            "organization" => $formattedOrder->organization,
            "rate" => $formattedOrder->rate,
            "store" => $formattedOrder->store,
            "addInfo" => $formattedOrder->shipmentAddressFull->addInfo,
        ];

        foreach ($formattedOrder->attributes as $attribute){
            $docBodyDemand["attributes"][] = [
                "meta" => $this->attributeHook->getDemandAttribute($attribute->name,$apiKey),
                "value" => $attribute->value,
            ];
        }

        if(property_exists($formattedOrder,"salesChannel")){
            $docBodyDemand["salesChannel"] = $formattedOrder->salesChannel;
        }

        if(property_exists($formattedOrder,"project")){
            $docBodyDemand["project"] = $formattedOrder->project;
        }

        if(property_exists($formattedOrder,"organizationAccount")){
            $docBodyDemand["organizationAccount"] = $formattedOrder->organizationAccount;
        }

        $createdDemand = $client->post($uri,$docBodyDemand);

        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/demand"."/".$createdDemand->id."/positions";
        //$client->setRequestUrl($uri);
        $entries = json_decode(json_encode($entries),true);
        $purchase = json_decode(json_encode($purchase),true);
        $delivery = json_decode(json_encode($delivery),true);
        $bodyDemandPositions = app(postController::class)
            ->metaPositions($apiKey,$entries,$purchase,$delivery);

        $client->post($uri,$bodyDemandPositions);

        $uri = 'https://online.moysklad.ru/api/remap/1.2/entity/demand'.'/'.$createdDemand->id;
        //$client->setRequestUrl($uri);
        $bodyOrder = [
            "customerOrder" => [
                "meta" => $meta,
            ],
        ];
        //dd($bodyOrder);
        return $client->put($uri,$bodyOrder)->meta;
    }

    /*private function createReturn($apiKey,$metaDemand,$formattedOrder,$entries)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn";
        $client = new MsClient($apiKey);
        $docBodyReturn = [
            "agent" => $formattedOrder['agent'],
            "organization" => $formattedOrder['organization'],
            "store" => $formattedOrder['store'],
            "demand" => [
                "meta" => $metaDemand,
            ],
        ];

        if(array_key_exists("salesChannel",$formattedOrder)){
            $docBodyReturn["salesChannel"] = $formattedOrder['salesChannel'];
        }

        if(array_key_exists("project",$formattedOrder)){
            $docBodyReturn["project"] = $formattedOrder['project'];
        }

        if(array_key_exists("organizationAccount",$formattedOrder)){
            $docBodyReturn["organizationAccount"] = $formattedOrder['organizationAccount'];
        }



        $createdReturn = $client->post($uri,$docBodyReturn);

        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/salesreturn"."/".$createdReturn->id."/positions";
        //$client->setRequestUrl($uri);
        foreach($entries as $entry) {
            $bodyReturnPositions = [
                0 => [
                    "quantity" => $entry['quantity'],
                    "price" => $entry['basePrice']* 100,
                    "assortment" => [
                        "meta" => app(PositionController::class)->searchProduct($entry['product'],$apiKey)
                    ],
                ],
            ];
            $client->post($uri,$bodyReturnPositions);
        }
        return $createdReturn->meta;
    }*/

    /*private function createPayOutDocument($apiKey,$metaReturn,$isPayment,$formattedOrder,$sum)
    {
        $uri = null;
        if ($isPayment == 2) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/paymentout";
        } elseif($isPayment == 1) {
            $uri = "https://online.moysklad.ru/api/remap/1.2/entity/cashout";
        }

        $client = new MsClient($apiKey);
        $docBody = [
            "agent" => $formattedOrder['agent'],
            "organization" => $formattedOrder['organization'],
            "expenseItem" => [
                "meta" => app(ExpenseItemController::class)->getExpenseItem('Возврат',$apiKey),
            ],
            "sum" => $sum*100,
            "operations" => [
                0=> [
                    "meta" => $metaReturn,
                ],
            ],
        ];


        if(array_key_exists("salesChannel",$formattedOrder)){
            $docBody["salesChannel"] = $formattedOrder['salesChannel'];
        }

        if(array_key_exists("project",$formattedOrder)){
            $docBody["project"] = $formattedOrder['project'];
        }

        if(array_key_exists("organizationAccount",$formattedOrder)){
            $docBody["organizationAccount"] = $formattedOrder['organizationAccount'];
        }

        $client->post($uri,$docBody);
    }*/

    /*    public function createDocuments($payments,$demands,$orderEntries,$statusOrder,$metaOrder,$paymentOption,$demandOption,$formattedOrder, $apiKey)
    {

    }*/

    /*    private function deletePayments($payments,$apiKey)
    {

    }*/

}
