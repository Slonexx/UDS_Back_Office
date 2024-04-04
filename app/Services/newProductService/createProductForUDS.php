<?php

namespace App\Services\newProductService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Models\ProductFoldersByAccountID;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use stdClass;

class createProductForUDS
{

    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function __construct($data)
    {
        $mainSetting = new getMainSettingBD($data['accountId']);

        $this->setting = json_decode(json_encode($data));
        $this->msClient = new MsClient($mainSetting->tokenMs);
        $this->udsClient = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
    }

    public function initialization(): array
    {
        $createProduct = new applicationCreatingProductForUDS($this->setting, $this->msClient, $this->udsClient);

        $ARR_PRODUCT = [];
        $find = ProductFoldersByAccountID::getInformation($this->setting->accountId);
        $baseUDS = $this->getUdsCheck();

        //dd($baseUDS);

        if ($find->toArray == null) return ["message" => "Отсутствуют настройки папок"];
        foreach ($find->toArray as $itemFolderModel) {
            $folderName = ($itemFolderModel['FolderName'] === "Корневая папка") ? '' : $itemFolderModel['FolderName'];


            (new folderCreating($this->msClient, $this->udsClient))->addCategoriesToUds($folderName);
            $productsMs = $this->getMs($folderName);

            foreach ($productsMs->rows as $item) {
                $is_create = $this->shouldCreateProductForCheck($item, $baseUDS);
                if ($is_create === false) continue;
                $is_create_sklad = $this->shouldCreateProduct($item);

                if (($is_create_sklad && strpos($item->pathName, $folderName) === 0 && substr_count($item->pathName, '/') < 3)) {
                    $createdProduct = $createProduct->createProductUds($item);
                    if ($createdProduct) $ARR_PRODUCT[] = $createdProduct;
                }
            }

        }

        return [
            "message" => "Successful export products to UDS",
            'Массив товаров' => $ARR_PRODUCT
        ];
    }


    private function shouldCreateProduct($item): bool
    {
        if ($this->setting->StoreRecord == '1') {
            $count = 0;

            $tmp = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/report/stock/all?filter=store=https://api.moysklad.ru/api/remap/1.2/entity/store/" . $this->setting->Store . ";search=" . $item->name);
            foreach ($tmp->rows as $itemStock) {
                $count += $itemStock->quantity;
            }
            return $count > 0;
        }
        return true;
    }

    private function shouldCreateProductForCheck($item, $baseUDS): bool
    {
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
    }


    private function getMs($folderName): stdClass
    {
        $urls = [
            '' => "https://api.moysklad.ru/api/remap/1.2/entity/product",
            'service' => "https://api.moysklad.ru/api/remap/1.2/entity/service"
        ];

        $result = new stdClass();
        $result->rows = [];

        foreach ($urls as $baseUrl) {
            $url = $baseUrl;
            if ($folderName !== '') $url .= "?filter=pathName~" . $folderName;

            $response = $this->msClient->get($url);
            $result->rows = array_merge($result->rows, $response->rows);
        }
        return $result;

    }


    public function getUdsCheck(): array
    {
        $result = [
            "productIds" => [],
            "categoryIds" => [],
            "externalCode" => [
                "product" => [],
                "category" => [],
            ],
            "isSet" => [
            ],
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
                        $result["externalCode"]['product'][] = $row->externalId;
                        $result["isSet"][$row->externalId] = $currId;
                    }

                } elseif ($row->data->type == "CATEGORY") {
                    $result["categoryIds"][] = $currId;
                    $newPath = $path . "/" . $row->name;
                    if (property_exists($row, 'externalId')) if ($row->externalId != null) $result["externalCode"]['category'][] = $row->externalId;
                    $this->findNodesUds($result, $currId, $newPath);
                }
            }

            $offset += 50;

        } while (count($rows) > 0);
    }
}
