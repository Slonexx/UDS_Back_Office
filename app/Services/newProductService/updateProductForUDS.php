<?php

namespace App\Services\newProductService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\ProductFoldersByAccountID;
use Illuminate\Support\Facades\Config;

class updateProductForUDS
{

    private mixed $setting;
    private getMainSettingBD $mainSetting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function __construct($data)
    {
        $this->mainSetting = new getMainSettingBD($data['accountId']);

        $this->setting = json_decode(json_encode($data));
        $this->msClient = new MsClient($this->mainSetting->tokenMs);
        $this->udsClient = new UdsClient($this->mainSetting->companyId, $this->mainSetting->TokenUDS);
    }

    public function initialization(): array
    {
        $ARR_PRODUCT = [];
        $find = ProductFoldersByAccountID::getInformation($this->setting->accountId);
        $baseUDS = $this->getUdsCheck();

        //dd($baseUDS, $this->setting);

        if ($find->toArray == null) return ["message" => "Отсутствуют настройки папок"];
        if ($baseUDS['rows'] != []) foreach ($baseUDS['rows'] as $item) {
            $url_att_id_uds = '';

            $url = Config::get("Global.JsonEndpoint")."/report/stock/all?filter=store=https://api.moysklad.ru/api/remap/1.2/entity/store/" . $this->setting->Store; //.
            $url_att = Config::get("Global.productAtt");

            $att = $this->msClient->newGet($url_att);
            if ($att->status) {
                if ($att->data->meta->size > 0) foreach ($att->data->rows as $row){
                    if ($row->name == "id (UDS)") $url_att_id_uds = $row;
                }
            }

            if ($item->externalId == null) {
                if ($url_att_id_uds != '') $url = $url . ';' . $url_att_id_uds->meta->href . '=' . $item->nodeId;
                else $url = $url . ";search=" . $item->name;
            } else {
                if ($item->data->type == "ITEM") $url = $url . ";product=" . 'https://api.moysklad.ru/api/remap/1.2/entity/product/' . $item->externalId;
                else $url = $url . ";search=" . $item->name;
            }

            $stock_in_url_product = $this->msClient->newGet($url);


            if ($stock_in_url_product->status) {
                $inStock = 0;
                $body_is_uds = $item;

                if ($item->data->type == "VARYING_ITEM") {
                    if ($stock_in_url_product->data->meta->size > 0) {
                        foreach ($stock_in_url_product->data->rows as $row) {
                            $inStock = 0;
                            foreach ($item->data->variants as $variant) {
                                if ($row->name == $variant->name) {
                                    if ($row->quantity > 0) $inStock = $row->quantity;
                                    $variant->inventory->inStock = $inStock;
                                }
                            }

                        }
                    }
                } else {
                    if ($body_is_uds->data->inventory == null) continue;
                    if ($stock_in_url_product->data->meta->size > 0) {
                        $inStock = $stock_in_url_product->data->rows[0]->quantity;
                        $body_is_uds->data->inventory->inStock = $inStock;
                    }
                }


                //dd(Config::get("Global.goods").$item->id);
                $this->udsClient->newPUT( Config::get("Global.goods").$item->id, $body_is_uds);
            } else continue;
        }

        /*OLD*/
        /*
       foreach ($find->toArray as $itemFolderModel) {
           $folderName = ($itemFolderModel['FolderName'] === "Корневая папка") ? '' : $itemFolderModel['FolderName'];


          $productsMs = $this->getMs($folderName);
           foreach ($productsMs->rows as $item) {
               if (in_array($item->id, $baseUDS["externalCode"]['product'])) {
                   if (property_exists($item, 'attributes'))
                       foreach ($item->attributes as $attribute) {

                           if ($attribute->name == "id (UDS)") {
                               if (!in_array($attribute->value, $baseUDS["productIds"])) {
                                   //dd($attribute, $attribute->meta, $baseUDS["isSet"][$item->id]);
                                   $updatedAttribute = [ "meta" => $attribute->meta, "value" => "".$baseUDS["isSet"][$item->id] ];
                                   $this->msClient->newPUT($item->meta->href, ["attributes" => [$updatedAttribute]]);
                               }
                           }

                       }
                   return false;
               }
               if (property_exists($item, "attributes")) {
                   foreach ($item->attributes as $attribute) {
                       if (($attribute->name == "id (UDS)" && in_array($attribute->value, $baseUDS["productIds"])) ||
                           ($attribute->name == "Не выгружать товар в UDS ? (UDS)" && $attribute->value)) {
                           return false;
                       }
                   }
               }
               return true;

               if (($is_create_sklad && strpos($item->pathName, $folderName) === 0 && substr_count($item->pathName, '/') < 3)) {
                   $createdProduct = $this->updateProduct($item);
                   if ($createdProduct) $ARR_PRODUCT[] = $createdProduct;
               }
           }

       }*/

        return [
            "message" => "Successful export products to UDS",
            'Массив товаров' => $ARR_PRODUCT
        ];
    }


    public function getUdsCheck(): array
    {
        $result = [
            "productIds" => [],
            "externalCode" => [
            ],
            "isSet" => [
            ],
            'rows' => []
        ];

        $this->findNodesUds($result);

        return $result;
    }

    private function findNodesUds(&$result, $nodeId = 0, $path = ""): void
    {
        $offset = 0;
        do {
            $url = "https://api.uds.app/partner/v2/goods?max=50&offset={$offset}";
            if ($nodeId > 0) $url .= "&nodeId={$nodeId}";


            $get = $this->udsClient->newGET($url);
            if ($get->status) $json = $get->data; else break;
            $rows = $json->rows ?? [];


            foreach ($rows as $row) {
                $currId = (string)$row->id;
                if ($row->data->type == "ITEM" || $row->data->type == "VARYING_ITEM") {
                    $result["productIds"][] = $currId;
                    if (property_exists($row, 'externalId')) if ($row->externalId != null) {
                        $result["externalCode"][] = $row->externalId;
                        $result["isSet"][$row->externalId] = $currId;
                        $result["rows"][] = $row;
                    }
                }elseif ($row->data->type == "CATEGORY") {
                    $newPath = $path . "/" . $row->name;
                    $this->findNodesUds($result, $currId, $newPath);
                }
            }

            $offset += 50;

        } while (count($rows) > 0);
    }

}
