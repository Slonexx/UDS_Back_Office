<?php

namespace App\Services\order;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Models\order_id;
use App\Services\AdditionalServices\DocumentService;
use App\Services\AdditionalServices\OrderStateService;
use App\Services\AdditionalServices\PositionService;
use App\Services\Settings\StateOrderSettings;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\DB;

class OrderUpdateMsService
{

    private StateOrderSettings $stateOrderSettings;
    private OrderStateService $orderStateService;
    private DocumentService $documentService;
    private PositionService $positionService;

    /**
     * @param StateOrderSettings $stateOrderSettings
     * @param OrderStateService $orderStateService
     * @param DocumentService $documentService
     * @param PositionService $positionService
     */
    public function __construct(
        StateOrderSettings $stateOrderSettings,
        OrderStateService $orderStateService,
        DocumentService $documentService,
        PositionService $positionService
    )
    {
        $this->stateOrderSettings = $stateOrderSettings;
        $this->orderStateService = $orderStateService;
        $this->documentService = $documentService;
        $this->positionService = $positionService;
    }


    public function updateOrdersMs($data)
    {
        $apiKeyMs = $data['tokenMs'];
        $companyId = $data['companyId'];
        $apiKeyUds = $data['apiKeyUds'];
        $accountId = $data['accountId'];
        $paymentOption = $data["paymentOpt"];
        $demandOption = $data["demandOpt"];

        //$ACCOUNT_ID = $data["accountId"];

        //$query = order_id::query();
        //$ids = $query->where('accountId',"=",$accountId)->;
        $results = DB::select('SELECT * FROM `order_ids` WHERE accountId = :accountId',
            ['accountId' => $accountId]);

        foreach ($results as $result){
            //echo ''.$result->orderID."<br>";
            //$orderId = 1856574;
            $orderId = $result->orderID;
            $orderInMs = $this->getMs($orderId,$apiKeyMs);
            if ($orderInMs->meta->size == 0) continue;
            else {
               $orderInMs = $orderInMs->rows[0];
            }
            $orderInUds = $this->getUds($orderId,$companyId,$apiKeyUds,$accountId);
            if ($orderInUds == null) continue;

            //dd($orderInMs);
            //dd($orderInUds);

            $stateUds = $orderInUds->state;
            $orderStateNameInMs = $this->getStatusNameByHref($orderInMs->state->meta->href,$apiKeyMs);
            $orderStateNameInUds = $this->stateOrderSettings->getStatusName($accountId,$stateUds);

            if ($orderStateNameInMs != $orderStateNameInUds){
                if ($orderInUds->state == "COMPLETED"){
                    try {
                        $this->documentService->initDocuments(
                            $orderInUds->items,
                            $orderInUds->purchase,
                            $orderInUds->delivery,
                            $paymentOption,
                            $demandOption,
                            $orderInMs,
                            $apiKeyMs
                        );
                    } catch (ClientException $e){
                        $bd = new BDController();
                        $bd->createUpdateOrder($accountId,$e->getMessage());
                    }
                }

                if ($orderInUds->state == "DELETED" || $orderInUds->state == "COMPLETED"){
                    $BD = new BDController();
                    $BD->deleteOrderID($accountId, $orderId);

                    if (property_exists($orderInMs,"positions")){
                        $client = new MsClient($apiKeyMs);
                        $jsonPositions = $client->get($orderInMs->positions->meta->href);
                        foreach ($jsonPositions->rows as $row){
                            $positionId = $row->id;
                            $orderIdInMs = $orderInMs->id;
                            if (property_exists($row,'reserve'))
                            if($row->reserve > 0){
                                $this->positionService->setPositionReserve($orderIdInMs,$positionId,0,$apiKeyMs);
                            }
                        }
                    }
                }

                $newMeta = $this->orderStateService->getState($accountId,$stateUds,$apiKeyMs);
                $this->changeOrderStatusMs($orderInMs->id,$newMeta,$apiKeyMs);

                $bd = new BDController();
                $bd->createUpdateOrder(
                    $accountId,"Все заказы в MS обновлены успешно! Обновлено заказов:".count($results)
                );
            }

        }

    }

    private function getMs($orderId,$apiKey)
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder?filter=externalCode=".$orderId;
        $client = new MsClient($apiKey);
        $json = $client->get($url);
        return $json;
    }

    private function getUds($orderIdUds,$companyId, $apiKey,$accountId)
    {
        $url = "https://api.uds.app/partner/v2/goods-orders/".$orderIdUds;
        $client = new UdsClient($companyId,$apiKey);
        try {
            return $client->get($url);
        } catch (ClientException $e){
            //dd($e->getMessage());
            $bd = new BDController();
            $bd->createUpdateOrder($accountId,$e->getMessage());
            return null;
        }
    }

    private function changeOrderStatusMs($orderId, $metaState, $apiKey)
    {
        $uri = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/".$orderId;
        $client = new MsClient($apiKey);
        $client->put($uri,[
            "state" => [
                "meta" => $metaState,
            ],
        ]);
    }

    private function getStatusNameByHref($href,$apiKey){
        $client = new MsClient($apiKey);
        $jsonStatus = $client->get($href);
        return $jsonStatus->name;
    }

}
