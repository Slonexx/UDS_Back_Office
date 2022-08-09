<?php

namespace App\Services;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Services\MetaServices\MetaHook\AttributeHook;
use App\Services\MetaServices\MetaHook\CurrencyHook;
use App\Services\MetaServices\MetaHook\PriceTypeHook;
use App\Services\MetaServices\MetaHook\UomHook;
use GuzzleHttp\Exception\ClientException;

class ProductService
{

    private AttributeHook $attributeHookService;
    private CurrencyHook $currencyHookService;
    private PriceTypeHook $priceTypeHookService;
    private UomHook $uomHookService;

    /**
     * @param AttributeHook $attributeHookService
     * @param CurrencyHook $currencyHookService
     * @param PriceTypeHook $priceTypeHookService
     * @param UomHook $uomHookService
     */
    public function __construct(
        AttributeHook $attributeHookService,
        CurrencyHook $currencyHookService,
        PriceTypeHook $priceTypeHookService,
        UomHook $uomHookService
    )
    {
        $this->attributeHookService = $attributeHookService;
        $this->currencyHookService = $currencyHookService;
        $this->priceTypeHookService = $priceTypeHookService;
        $this->uomHookService = $uomHookService;
    }

    //Add products to MS from UDS

    private function getMsCheck($apiKeyMs)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        $client = new MsClient($apiKeyMs);
        $json = $client->get($url);
        $propertyIds = [];
        $propertyExt = [];

        foreach ($json->rows as $row){
            $propertyExt[] = $row->externalCode;
            if (property_exists($row, 'attributes')){
                foreach ($row->attributes as $attribute){
                    if ($attribute->name == "id (UDS)"){
                        $propertyIds[] = $attribute->value;
                    }
                }
            }
        }
        return [
            "ids" => $propertyIds,
            "externals" => $propertyExt,
        ];
    }

    private function getUds($companyId, $apiKeyUds)
    {
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);
        return $client->get($url);
    }

    private function notAddedInMs($apiKeyMs,$apiKeyUds,$companyId)
    {
        $productsMs = $this->getMsCheck($apiKeyMs);
        $productsUds = $this->getUds($companyId,$apiKeyUds);

        //$count = 0;
        foreach ($productsUds->rows as $productUds){
            $currId = "".$productUds->id;
            if (!in_array($currId, $productsMs["ids"])){
                if ($productUds->data->type == "ITEM"){
                    $this->createProductMs($apiKeyMs,$productUds);
                   // $count++;
                }
                elseif ($productUds->data->type == "VARYING_ITEM"){
                    $this->createVariantProduct($apiKeyMs,$productUds);
                   // $count++;
                }
                elseif ($productUds->data->type == "CATEGORY"){
                  $category = $this->createCategoryMs($apiKeyMs,$productUds->name,$productUds->id);
                  set_time_limit(600);
                  $this->addProductsByCategoryUds(
                      $productsMs["ids"],
                      $category->meta,
                      $productUds->id,
                      $companyId,
                      $apiKeyUds,
                      $apiKeyMs
                  );
                }
            }
        }

        return [
            "message" => "Successful export products to MS",
        ];

    }

    private function addProductsByCategoryUds(
        $productIds,$parentCategoryMeta,$nodeId, $companyId, $apiKeyUds,$apiKeyMs
    ){
        $url = "https://api.uds.app/partner/v2/goods?nodeId=".$nodeId;
        $client = new UdsClient($companyId,$apiKeyUds);
        $json = $client->get($url);

        if (count($json->rows) == 0) return;

            foreach ($json->rows as $row){
                $currId = "".$row->id;
                if ($row->data->type == "CATEGORY"){
                    $category = $this->createCategoryMs($apiKeyMs,$row->name,$row->id,$parentCategoryMeta);
                   // dd($category->pathName);
                    $this->addProductsByCategoryUds(
                        $productIds,
                        $category->meta,
                        $row->id,
                        $companyId,
                        $apiKeyUds,
                        $apiKeyMs
                    );
                }
                elseif ($row->data->type == "ITEM"){
                    if (!in_array($currId,$productIds)){
                        $this->createProductMs($apiKeyMs,$row,$parentCategoryMeta);
                    }
                }
                elseif ($row->data->type == "VARYING_ITEM"){
                    if (!in_array($currId,$productIds)){
                        $this->createVariantProduct($apiKeyMs,$row,$parentCategoryMeta);
                    }
                }
            }

    }

    public function insertToMs($data)
    {
       return $this->notAddedInMs(
            $data['tokenMs'],
            $data['apiKeyUds'],
            $data['companyId']
        );
    }

    private function createCategoryMs($apiKeyMs, $nameFolder,$externalCode,$parentFolder = null)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder";
        $client = new MsClient($apiKeyMs);

        $jsonToCheck = $client->get($url);

        $foundedCategory = null;
        foreach ($jsonToCheck->rows as $row){
            if ($row->externalCode == $externalCode){
                $foundedCategory = $row;
                break;
            }
        }

        if ($foundedCategory != null){
            return $foundedCategory;
        } else {
            //dd($nameFolder,$pathName);
            $bodyCategory["name"]= $nameFolder;
            $bodyCategory["externalCode"] = "".$externalCode;
            if ($parentFolder != null){
                $bodyCategory["productFolder"] = [
                    "meta" => $parentFolder,
                ];
            }
            return $client->post($url,$bodyCategory);
        }
    }

    private function createProductMs($apiKeyMs, $productUds, $productFolderMeta = null)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        $bodyProduct["name"] = $productUds->name;

        $bodyProduct["salePrices"] = [
            0 => [
                "value" => $productUds->data->price * 100,
                "currency" => $this->currencyHookService->getKzCurrency($apiKeyMs),
                "priceType" => $this->priceTypeHookService->getPriceType("Цена продажи",$apiKeyMs),
            ],
        ];

        $nameUom = $this->getUomMsByUds($productUds->data->measurement);
        $bodyProduct["uom"] = $this->uomHookService->getUom($nameUom,$apiKeyMs);

        if ($productUds->data->sku != null){
            $bodyProduct["article"] = $productUds->data->sku;
        }

        $countAttribute = 0;
        if ($productUds->data->offer != null){
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Акционный товар (UDS)", $apiKeyMs),
                "name" => "Акционный товар (UDS)",
                "value" => true,
            ];
            $countAttribute++;
            if ($productUds->data->offer->skipLoyalty){
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Не применять бонусную программу (UDS)", $apiKeyMs),
                    "name" => "Не применять бонусную программу (UDS)",
                    "value" => true,
                ];
                $countAttribute++;
            }
            $bodyProduct["salePrices"][1] = [
                "value" => $productUds->data->offer->offerPrice * 100,
                "currency" => $this->currencyHookService->getKzCurrency($apiKeyMs),
                "priceType" => $this->priceTypeHookService->getPriceType("Акционный",$apiKeyMs),
            ];
        }

        if ($productUds->data->increment != null){
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Шаг дробного значения (UDS)",$apiKeyMs),
                "name" => "Шаг дробного значения (UDS)",
                "value" => floatval($productUds->data->increment),
            ];
            $countAttribute++;
        }

        if ($productUds->data->minQuantity != null){
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Минимальный размер заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Минимальный размер заказа дробного товара (UDS)",
                "value" => floatval($productUds->data->minQuantity),
            ];
            $countAttribute++;
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Дробное значение товара (UDS)",$apiKeyMs),
                "name" => "Дробное значение товара (UDS)",
                "value" => true,
            ];
            $countAttribute++;
        }

        if ($nameUom == "METRE"){

            //if ($countAttribute == 0) $countAttribute++;

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Цена минимального размера заказа дробного товара (UDS)",
                "value" => $productUds->data->price / 100.0,
            ];
            $countAttribute++;
        }
        elseif ($nameUom == "LITRE" || $nameUom == "KILOGRAM"){

           // if ($countAttribute == 0) $countAttribute++;

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Цена минимального размера заказа дробного товара (UDS)",
                "value" => $productUds->data->price / 1000.0,
            ];
            $countAttribute++;
        }

        //if ($countAttribute == 0) $countAttribute++;

        $bodyProduct["attributes"][$countAttribute] = [
            "meta" => $this->attributeHookService
                ->getProductAttribute("id (UDS)",$apiKeyMs),
            "name" => "id (UDS)",
            "value" => "".$productUds->id,
        ];

        if ($productFolderMeta != null){
            $bodyProduct["productFolder"] = [
                "meta" => $productFolderMeta,
            ];
        }

        //dd($bodyProduct);

        $client = new MsClient($apiKeyMs);
        try {
            $client->post($url,$bodyProduct);
        } catch (ClientException $e){
            dd($e);
        }

    }

    private function createVariantProduct($apiKeyMs, $productVar, $productFolderMeta = null){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        $client = new MsClient($apiKeyMs);
        foreach ($productVar->data->variants as $variant){
            $bodyProductVar["name"] = $variant->name."(".$productVar->name.")";
            if ($variant->sku != null){
                $bodyProductVar["article"] = $variant->sku;
            }
            $bodyProductVar["salePrices"] = [
                0 => [
                    "value" => $variant->price * 100,
                    "currency" => $this->currencyHookService->getKzCurrency($apiKeyMs),
                    "priceType" => $this->priceTypeHookService->getPriceType("Цена продажи",$apiKeyMs),
                ],
            ];
            $bodyProductVar["uom"] = $this->uomHookService->getUom("шт",$apiKeyMs);
            $countAttribute = 0;
            if ($variant->offer != null){
                $bodyProductVar["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Акционный товар (UDS)", $apiKeyMs),
                    "name" => "Акционный товар (UDS)",
                    "value" => true,
                ];
                $countAttribute++;
                if ($variant->offer->skipLoyalty){
                    $bodyProductVar["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Не применять бонусную программу (UDS)", $apiKeyMs),
                        "name" => "Не применять бонусную программу (UDS)",
                        "value" => true,
                    ];
                    $countAttribute++;
                }
                $bodyProductVar["salePrices"][1] = [
                    "value" => $variant->offer->offerPrice * 100,
                    "currency" => $this->currencyHookService->getKzCurrency($apiKeyMs),
                    "priceType" => $this->priceTypeHookService->getPriceType("Акционный",$apiKeyMs),
                ];
            }
            $bodyProductVar["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("id (UDS)",$apiKeyMs),
                "name" => "id (UDS)",
                "value" => "".$productVar->id,
            ];

            if ($productFolderMeta != null){
                $bodyProductVar["productFolder"] = [
                    "meta" => $productFolderMeta,
                ];
            }

            try{
                $client->post($url,$bodyProductVar);
            } catch (ClientException $e){
                dd($e);
            }
        }
    }

    private function getUomMsByUds($nameUom): string
    {
        $nameUomMs = "";
        switch ($nameUom){
            case "PIECE":
                $nameUomMs = "шт";
                break;
            case "CENTIMETRE":
                $nameUomMs = "см";
                break;
            case "METRE":
                $nameUomMs = "м";
                break;
            case "MILLILITRE":
                $nameUomMs = "мм";
                break;
            case "LITRE":
                $nameUomMs = "л; дм3";
                break;
            case "GRAM":
                $nameUomMs = "г";
                break;
            case "KILOGRAM":
                $nameUomMs = "кг";
                break;
        }
        return $nameUomMs;
    }

    //Add products to UDS from MS
    private function getUdsCheck($companyId, $apiKeyUds){
        $this->findNodesUds($nodeIds,$companyId,$apiKeyUds);
        return $nodeIds;
    }

    private function getMs($apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
    }

    private function getCategoriesMs($apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder";
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
    }

    private function updateCategory($createdCategoryId,$idMs,$apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$idMs;
        $client = new MsClient($apiKeyMs);
        $body = [
            "externalCode" => "".$createdCategoryId,
        ];
        $client->put($url,$body);
    }

    private function notAddedInUds($apiKeyMs,$apiKeyUds,$companyId){
        $productsUds = $this->getUdsCheck($companyId,$apiKeyUds);
        //dd($productsUds);
        $categoriesMs = $this->getCategoriesMs($apiKeyMs);
        foreach ($categoriesMs->rows as $categoryMs){
            if (!in_array($categoryMs->externalCode,$productsUds["categoryIds"])){
               $createdCategoryId = $this->createCategoryUds($categoryMs->name,$companyId,$apiKeyUds)->id;
               $this->updateCategory($createdCategoryId, $categoryMs->id,$apiKeyMs);
            }
        }

        $productsMs = $this->getMs($apiKeyMs);

        foreach ($productsMs->rows as $row){

            $isProductNotAdd = false;

            if (property_exists($row,"attributes")){
                $foundedIdAttrib = false;
                foreach ($row->attributes as $attribute){
                    if ($attribute->name == "id (UDS)"){
                        $foundedIdAttrib = true;
                        if (!in_array($attribute->value,$productsUds["productIds"]))
                        {
                            $isProductNotAdd = true;
                        }
                        break;
                    }
                }
                if (!$foundedIdAttrib) $isProductNotAdd = true;
            }

            if ($isProductNotAdd){
                if (property_exists($row,"productFolder")){
                    $productFolderHref = $row->productFolder->meta->href;
                    $idNodeCategory = $this->getCategoryNameByMetaHref($productFolderHref,$apiKeyMs);
                    //dd($idNodeCategory);
                    $this->createProductUds($row,$apiKeyMs,$companyId,$apiKeyUds,$idNodeCategory);
                } else {
                    $this->createProductUds($row,$apiKeyMs,$companyId,$apiKeyUds);
                }
            }

        }

        return [
            "message" => "Successful export products to UDS"
        ];
    }

    public function insertToUds($data)
    {
       return $this->notAddedInUds(
            $data['tokenMs'],
            $data['apiKeyUds'],
            $data['companyId']
        );
    }

    private function createProductUds($product,$apiKeyMs,$companyId,$apiKeyUds,$nodeId = 0){
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);
xsacx
        $nameOumUds = $this->getUomUdsByMs($product->uom->meta->href,$apiKeyMs);
        $body = [
            "name" => $product->name,
            "data" => [
                "type" => "ITEM",
                "price" => $product->salePrices[0]->value,
                "measurement" => $nameOumUds,
            ],
        ];

        if (property_exists($product,"attributes")){
            foreach ($product->attributes as $attribute){
                if ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value == 1){
                    $body["data"]["offer"]["skipLoyalty"] = true;
                } elseif ($attribute->name == "Шаг дробного значения (UDS)"){
                    $body["data"]["increment"] = $attribute->value;
                } elseif ($attribute->name == "Минимальный размер заказа дробного товара (UDS)"){
                    $body["data"]["minQuantity"] = $attribute->value;
                } elseif ($attribute->name == "Ограничен товар (UDS)" && $attribute->value == 1){
                    $body["data"]["inventory"]["inStock"] = null;
                }
            }
        }

        if (property_exists($product, "article")){
            $body["data"]["sku"] = $product->article;
        }

        if ($nodeId > 0){
            $body["nodeId"] = intval($nodeId);
        }

        dd($body);

       return $client->post($url,$body);
    }

    private function createCategoryUds($nameCategory,$companyId,$apiKeyUds,$nodeId = 0){
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);
        $body = [
            "name" => $nameCategory,
            "data" => [
                "type" => "CATEGORY",
            ],
        ];

        if ($nodeId > 0){
            $body["nodeId"] = $nodeId;
        }

        return $client->post($url, $body);
    }

    private function findNodesUds(&$result,$companyId, $apiKeyUds,$nodeId = 0, $path=""): void
    {
        if ($nodeId > 0 ){
            $url = "https://api.uds.app/partner/v2/goods?nodeId=".$nodeId;
            //dd($url);
        }
        else {
            $url = "https://api.uds.app/partner/v2/goods";
        }

        $client = new UdsClient($companyId,$apiKeyUds);
        $json = $client->get($url);

        if (count($json->rows) == 0) {
            return;
        }

        foreach ($json->rows as $row) {
            $currId = "".$row->id;
            if ($row->data->type == "ITEM" || $row->data->type == "VARYING_ITEM"){
/*                $result["products"][] = [
                    "id" => $currId,
                    "name" => $row->name,
                    "path" => $path,
                ];*/
                $result["productIds"][] = $currId;
            }
            elseif ($row->data->type == "CATEGORY"){
/*                $result ["categories"][] = [
                    "id" => $currId,
                    "name" => $row->name,
                    "path" => $path,
                ];*/
                $result["categoryIds"][] = $currId;
                $newPath = $path."/".$row->name;
                $this->findNodesUds($result,$companyId,$apiKeyUds,$currId,$newPath);
            }
        }
    }

    private function getCategoryNameByMetaHref($href, $apiKeyMs){
        $client = new MsClient($apiKeyMs);
        return $client->get($href)->externalCode;
    }

    private function getUomUdsByMs($href, $apiKeyMs): string
    {
        $client = new MsClient($apiKeyMs);
        $json = $client->get($href);

        $nameUomUds = "";
        switch ($json->name){
            case "шт":
                $nameUomUds = "PIECE";
                break;
            case "см":
                $nameUomUds = "CENTIMETRE";
                break;
            case "м":
                $nameUomUds = "METRE";
                break;
            case "мм":
                $nameUomUds = "MILLILITRE";
                break;
            case "л; дм3":
                $nameUomUds = "LITRE";
                break;
            case "г":
                $nameUomUds = "GRAM";
                break;
            case "кг":
                $nameUomUds = "KILOGRAM";
                break;
        }
        return $nameUomUds;
    }

}
