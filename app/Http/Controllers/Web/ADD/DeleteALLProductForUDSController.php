<?php

namespace App\Http\Controllers\Web\ADD;

use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Services\product\ProductCreateUdsService;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\JsonResponse;

class DeleteALLProductForUDSController extends Controller
{

    public function DeleteALLProductForUDSController($as, $accountId): JsonResponse
    {

        set_time_limit(900);

        if ($as == "p330538"){
            $result = [];
            $setting = new getSettingVendorController($accountId);
            $UDS = $this->getUdsCheck($setting->companyId, $setting->TokenUDS, $accountId);

            $Client = new UdsClient($setting->companyId, $setting->TokenUDS);
            if ($UDS['productIds']!=[]){
                foreach ($UDS['productIds'] as $item){
                    try {
                        $Client->delete("https://api.uds.app/partner/v2/goods/".$item);
                        $result[] = "Удаленно = ".$item;
                    } catch (BadResponseException){
                        $result[] = "Не удалось удалить = ".$item;
                        continue;
                    }
                }
            }
            return response()->json($result);
        } else return response()->json([],404);

    }


    public function getUdsCheck($companyId, $apiKeyUds, $accountId): array
    {
        $result = [
            "productIds" => [],
            "categoryIds" => [],
        ];

        $this->findNodesUds($result, $companyId, $apiKeyUds, $accountId);

        return $result;
    }

    private function findNodesUds(&$result, $companyId, $apiKeyUds, $accountId, $nodeId = 0, $path = ""): void
    {
        $offset = 0;

        $client = new UdsClient($companyId, $apiKeyUds);

        do {
            $url = "https://api.uds.app/partner/v2/goods?max=50&offset={$offset}";

            if ($nodeId > 0) {
                $url .= "&nodeId={$nodeId}";
            }

            try {
                $json = $client->get($url);
                $rows = $json->rows ?? [];
            } catch (ClientException $e) {
                break; // Прерываем цикл в случае ошибки
            }

            foreach ($rows as $row) {
                $currId = (string) $row->id;
                if ($row->data->type == "ITEM" || $row->data->type == "VARYING_ITEM") {
                    $result["productIds"][] = $currId;
                } elseif ($row->data->type == "CATEGORY") {
                    $result["categoryIds"][] = $currId;
                    $newPath = $path . "/" . $row->name;
                    $this->findNodesUds($result, $companyId, $apiKeyUds, $accountId, $currId, $newPath);
                }
            }

            $offset += 50;

        } while (count($rows) > 0);
    }
}
