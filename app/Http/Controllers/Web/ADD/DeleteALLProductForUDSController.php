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
        $this->findNodesUds($nodeIds, $companyId, $apiKeyUds, $accountId);
        if ($nodeIds == null) {
            $nodeIds = [
                "productIds" => [],
                "categoryIds" => [],
            ];
        }
        return $nodeIds;
    }

    private function findNodesUds(&$result, $companyId, $apiKeyUds, $accountId, $nodeId = 0, $path = ""): void
    {
        $offset = 0;
        while ($this->haveRowsInResponse($url, $offset, $companyId, $apiKeyUds, $nodeId)) {
            $client = new UdsClient($companyId, $apiKeyUds);
            $json = $client->get($url);
            foreach ($json->rows as $row) {
                $currId = "" . $row->id;
                if ($row->data->type == "ITEM" || $row->data->type == "VARYING_ITEM") {
                    $result["productIds"][] = $currId;
                } elseif ($row->data->type == "CATEGORY") {
                    $result["categoryIds"][] = $currId;
                    $newPath = $path . "/" . $row->name;
                    $this->findNodesUds($result, $companyId, $apiKeyUds, $accountId, $currId, $newPath);
                }
            }
            $offset += 50;
        }

    }

    private function haveRowsInResponse(&$url, $offset, $companyId, $apiKeyUds, $nodeId = 0): bool
    {
        $url = "https://api.uds.app/partner/v2/goods?max=50&offset=" . $offset;
        if ($nodeId > 0) {
            $url = $url . "&nodeId=" . $nodeId;
        }
        $client = new UdsClient($companyId, $apiKeyUds);
        try {
            $json = $client->get($url);
            return count($json->rows) > 0;
        } catch (ClientException) {
            return false;
        }
    }
}
