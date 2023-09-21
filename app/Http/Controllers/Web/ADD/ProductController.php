<?php

namespace App\Http\Controllers\Web\ADD;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function infoProduct(Request $request){
        $accountId = $request->accountId;
        $productID = $request->productID;
        $Setting = new getSettingVendorController($accountId);

        if ($Setting->ProductFolder != "1") {
            return response()->json([
                'code' => 205,
                'message' => "Отсутствует настройки отправки товара в UDS"
            ], 200);
        }

        $ClientUDS = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $ClientMS = new MsClient($Setting->TokenMoySklad);
        try {
            $ClientUDS->get('https://api.uds.app/partner/v2/settings');
        } catch (BadResponseException $e){
            if ($e->getCode() == 401){
                return response()->json([
                    'code' => 205,
                    'message' => "Проверти настройки приложения, не верный ID компании и API Key"
                ], 200);
            } else {
                return response()->json([
                    'code' => 205,
                    'message' => $e->getMessage()
                ], 200);
            }
        }

        try {
            $BodyMSProduct = $ClientMS->get('https://api.moysklad.ru/api/remap/1.2/entity/product/'.$productID);
        } catch (BadResponseException $e){
            if ($e->getCode() == 401){
                return response()->json([
                    'code' => 205,
                    'message' => "Умер токен МоегоСклада, возможно приложение приостановлено"
                ], 200);
            } else {
                return response()->json([
                    'code' => 205,
                    'message' => $e->getMessage()
                ], 200);
            }
        }

        $ID = 0;
        $ProductToUDS = false;
        if (property_exists($BodyMSProduct, 'attributes')){
            foreach ($BodyMSProduct->attributes as $item){
                if ($item->name == "id (UDS)"){
                    $ID = $item->value;
                }
            }
        }
        if ($ID != 0) {
            try {
                $BodyUDS = $ClientUDS->get('https://api.uds.app/partner/v2/goods/'.$ID);
                $ProductToUDS = true;
            } catch (BadResponseException $e) {
                return response()->json([
                    'code' => 205,
                    'message' => $e->getMessage()
                ], 200);
            }
            $MainName = $BodyUDS->name;
            $nodeId = $this->nodeId($BodyMSProduct, $ClientMS, $ClientUDS);
            $Measurement = $BodyUDS->data->measurement;
            $offer = $BodyUDS->data->offer;
            $price = $BodyUDS->data->price;
            $sku = $BodyUDS->data->sku;
        }

        $Category = $this->Category($ClientUDS);


        return response()->json([
            'code' => 200,
            'message' => "",

            'ProductToUDS' => $ProductToUDS,
            'MainName' => $MainName,
            'nodeId' => $nodeId,
            'Measurement' => $Measurement,
            'offer' => $offer,
            'price' => $price,
            'sku' => $sku,

            'Category' => $Category,

        ], '200');
    }

    private function nodeId(mixed $BodyMSProduct, MsClient $ClientMS, UdsClient $udsClient)
    {
        if (property_exists($BodyMSProduct, 'productFolder')){
            $productFolder = $ClientMS->get($BodyMSProduct->productFolder->meta->href);
            try {
                $udsClient->get('https://api.uds.app/partner/v2/goods/'.$productFolder->externalCode);
                return $productFolder->externalCode;
            } catch (BadResponseException $e) {
                return null ;
            }
        } else return null ;
    }

    private function Category(UdsClient $ClientUDS)
    {
        set_time_limit(3600);
        $this->findNodesUds($nodeIds, $ClientUDS);
        return $nodeIds;
    }

    private function findNodesUds(&$result,UdsClient $ClientUDS,$nodeId = 0, $path=""): void
    {
        $offset = 0;
        while ($this->haveRowsInResponse($url, $offset, $ClientUDS, $nodeId)){
            $json = $ClientUDS->get($url);
            foreach ($json->rows as $row) {
                if ($row->data->type == "CATEGORY"){
                    $result[] =[ 'id' => $row->id, 'name' => $row->name ];
                    $newPath = $path."/".$row->name;
                    $this->findNodesUds($result, $ClientUDS, $row->id, $newPath);
                }
            }
            $offset += 50;
        }

    }

    private function haveRowsInResponse(&$url, $offset, UdsClient $ClientUDS, $nodeId=0): bool
    {
        $url = "https://api.uds.app/partner/v2/goods?max=50&offset=".$offset;
        if ($nodeId > 0){ $url = $url."&nodeId=".$nodeId; }

        try {
            $json = $ClientUDS->get($url);
            return count($json->rows) > 0;
        }catch (ClientException $e){
            return false;
        }
    }

}
