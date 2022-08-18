<?php

namespace App\Services\AdditionalServices;

use App\Components\MsClient;

class DocumentService
{

    public function initDocuments($orderEntries,$statusOrder,$metaOrder,$paymentOption,$demandOption,$formattedOrder, $apiKey)
    {

    }

    private function createFactureout($apiKey,$metaDemand)
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
            "agent" => $formattedOrder['agent'],
            "organization" => $formattedOrder['organization'],
            "rate" => $formattedOrder['rate'],
            "sum" => $sum*100,
            "operations" => [
                0=> [
                    "meta" => $meta,
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
    }

    private function createDenamd($apiKey, $meta, $formattedOrder, $entries)
    {
        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/demand";
        $client = new MsClient($apiKey);
        $docBodyDemand = [
            "agent" => $formattedOrder['agent'],
            "organization" => $formattedOrder['organization'],
            "rate" => $formattedOrder['rate'],
            "store" => $formattedOrder['store'],
            "addInfo" => $formattedOrder['shipmentAddressFull']['addInfo'],
        ];


        if(array_key_exists("salesChannel",$formattedOrder)){
            $docBodyDemand["salesChannel"] = $formattedOrder['salesChannel'];
        }

        if(array_key_exists("project",$formattedOrder)){
            $docBodyDemand["project"] = $formattedOrder['project'];
        }

        if(array_key_exists("organizationAccount",$formattedOrder)){
            $docBodyDemand["organizationAccount"] = $formattedOrder['organizationAccount'];
        }



        $createdDemand = $client->post($uri,$docBodyDemand);

        $uri = "https://online.moysklad.ru/api/remap/1.2/entity/demand"."/".$createdDemand->id."/positions";
        //$client->setRequestUrl($uri);
        foreach($entries as $entry) {
            $bodyDemandPositions = [
                "quantity" => $entry['quantity'],
                "price" => $entry['basePrice']* 100,
                "assortment" => [
                    "meta" => app(PositionController::class)->searchProduct($entry['product'],$apiKey)
                ],
            ];
            $client->post($uri,$bodyDemandPositions);
        }

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

    private function createReturn($apiKey,$metaDemand,$formattedOrder,$entries)
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
    }

    private function createPayOutDocument($apiKey,$metaReturn,$isPayment,$formattedOrder,$sum)
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
    }

    public function createDocuments($payments,$demands,$orderEntries,$statusOrder,$metaOrder,$paymentOption,$demandOption,$formattedOrder, $apiKey)
    {

    }

    private function deletePayments($payments,$apiKey)
    {

    }

}
