<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class ProductUpdateUdsHiddenService
{
    private getMainSettingBD $setting;
    private UdsClient $client;
    private MsClient $msClient;

    private getSettingVendorController $getSettingVendor;

    public function insertUpdate($data): void
    {
        $this->setting = new getMainSettingBD($data['accountId']);
        $this->getSettingVendor = new getSettingVendorController($data['accountId']);
        $this->client = new UdsClient($this->getSettingVendor->companyId, $this->getSettingVendor->TokenUDS);
        $this->msClient = new MsClient($this->getSettingVendor->TokenMoySklad);
        $this->startUpdate();
    }


    private function startUpdate(): void
    {
        set_time_limit(3600);
        if (true) {
            $productsUds = $this->getUdsCheck();
            if (!array_key_exists('categoryIds', $productsUds)) { $productsUds['categoryIds'] = []; }
            if (array_key_exists('productIds', $productsUds)) {
                $filter = null;
                foreach ($this->msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes/')->rows as $item) {
                    if ($item->name == 'id (UDS)') {
                        $filter = $item->meta->href;
                    } else continue;
                }
                if ($filter != null) {
                    foreach ($productsUds['productIds'] as $item) {
                        try {
                            $nodeID = 0;
                            $product = $this->client->get('https://api.uds.app/partner/v2/goods/' . $item);
                            $MS_product = $this->msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/product?filter='.$filter.'='.$item)->rows;
                            if ($MS_product != [] and $product->hidden == false){
                                $MS_product = $MS_product[0];
                                if (property_exists($product->data, 'variants')) {
                                    $countVars = 0;
                                    foreach ($product->data->variants as $item_var) {

                                        if (property_exists($item_var->inventory, 'inStock')) {
                                            if ($item_var->inventory->inStock === null) {
                                                break;
                                            } elseif ($item_var->inventory->inStock === 0) {
                                                $countVars = $countVars + 1;
                                            }
                                        } else  break;
                                    }
                                    if (count($product->data->variants) == $countVars) {
                                        $postUDS = json_decode(json_encode($product), true);
                                        $postUDS['hidden'] = true;
                                        if (property_exists($MS_product, 'productFolder')){
                                            $nodeID = $this->msClient->get($MS_product->productFolder->meta->href)->externalCode;
                                            if (is_numeric($nodeID) && ctype_digit($nodeID)){ $postUDS['nodeId'] = $nodeID; }
                                        }
                                        $this->client->put('https://api.uds.app/partner/v2/goods/' . $item, $postUDS);
                                    }
                                } else {
                                    if (property_exists($product->data->inventory, 'inStock')) {
                                        $inStock = false;
                                        if ($product->data->inventory->inStock === 0) { $inStock = true; }
                                        if ($inStock) {
                                            $postUDS = json_decode(json_encode($product), true);
                                            $postUDS['hidden'] = true;
                                            //$nodeID = $this->msClient->get()
                                            if (property_exists($MS_product, 'productFolder')){
                                                $nodeID = $this->msClient->get($MS_product->productFolder->meta->href)->externalCode;
                                                if (is_numeric($nodeID) && ctype_digit($nodeID)){
                                                    $postUDS['nodeId'] = $nodeID;
                                                }
                                            }
                                            $this->client->put('https://api.uds.app/partner/v2/goods/' . $item, $postUDS);
                                        }
                                    } else continue;
                                }
                            }
                        } catch (BadResponseException $e){
                            continue;
                        }
                    }
                }


            }
        }
    }

    public function getUdsCheck(): array
    {
        set_time_limit(3600);
        $this->findNodesUds($nodeIds, $this->getSettingVendor->companyId, $this->getSettingVendor->TokenUDS, $this->getSettingVendor->accountId);
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
        while ($this->haveRowsInResponse($url, $offset, $companyId, $apiKeyUds, $accountId, $nodeId)) {
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

    private function haveRowsInResponse(&$url, $offset, $companyId, $apiKeyUds, $accountId, $nodeId = 0): bool
    {
        $url = "https://api.uds.app/partner/v2/goods?max=50&offset=" . $offset;
        if ($nodeId > 0) {
            $url = $url . "&nodeId=" . $nodeId;
        }
        $client = new UdsClient($companyId, $apiKeyUds);
        try {
            $json = $client->get($url);
            return count($json->rows) > 0;
        } catch (ClientException $e) {
            $bd = new BDController();
            $bd->errorProductLog($accountId, $e->getMessage());
            return false;
        }
    }

}
