<?php

namespace App\Http\Controllers\Web\ADD;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use React\EventLoop\Loop;
use function React\Promise\all;

class DeleteALLProductForUDSController extends Controller
{

    public function DeleteALLProductForUDSController($as, $accountId): JsonResponse
    {

        set_time_limit(900);

        $result = [];
        $setting = new getSettingVendorController($accountId);
        $Client = new UdsClient($setting->companyId, $setting->TokenUDS);
        $UDS = $this->getUdsCheck($setting->companyId, $setting->TokenUDS, $accountId, $Client);

        foreach ($UDS as $base) {
            foreach ($base as $item){
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

    }

    public function DeleteProduct($accountId){
        $result = [];
        $setting = new getSettingVendorController($accountId);
        $Client = new UdsClient($setting->companyId, $setting->TokenUDS);
        $UDS = $this->getUdsCheck($setting->companyId, $setting->TokenUDS, $accountId, $Client);

        foreach ($UDS as $base) {
            foreach ($base as $item){
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
    }


    public function getUdsCheck($companyId, $apiKeyUds, $accountId, UdsClient $client): array
    {
        $result = [
            "productIds" => [],
            "categoryIds" => [],
        ];

        $this->findNodesUds($result, $companyId, $apiKeyUds, $accountId, $client);

        return $result;
    }

    private function findNodesUds(&$result, $companyId, $apiKeyUds, $accountId, UdsClient $client, $nodeId = 0, $path = ""): void
    {
        $offset = 0;
        $url = "https://api.uds.app/partner/v2/goods?max=50&offset={$offset}";
        $get = $client->newGET($url);
        if ($get->status) $json = $get->data;



        do {
            $url = "https://api.uds.app/partner/v2/goods?max=50&offset={$offset}";
            if ($nodeId > 0) $url .= "&nodeId={$nodeId}";



            $get = $client->newGET($url);
            if ($get->status) $json = $get->data; else break;
            $rows = $json->rows ?? [];


            foreach ($rows as $row) {
                $currId = (string) $row->id;
                if ($row->data->type == "ITEM" || $row->data->type == "VARYING_ITEM") $result["productIds"][] = $currId;
                elseif ($row->data->type == "CATEGORY") {
                    $result["categoryIds"][] = $currId;
                    $newPath = $path . "/" . $row->name;
                    $this->findNodesUds($result, $companyId, $apiKeyUds, $accountId,$client, $currId, $newPath);
                }
            }

            $offset += 50;

        } while (count($rows) > 0);
    }
}
