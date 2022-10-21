<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Services\AdditionalServices\ImgService;
use App\Services\AdditionalServices\StockProductService;
use App\Services\MetaServices\Entity\StoreService;
use App\Services\MetaServices\MetaHook\AttributeHook;
use App\Services\MetaServices\MetaHook\CurrencyHook;
use App\Services\MetaServices\MetaHook\PriceTypeHook;
use App\Services\MetaServices\MetaHook\UomHook;
use GuzzleHttp\Exception\ClientException;

class ProductCreateUdsService
{
    private AttributeHook $attributeHookService;
    private CurrencyHook $currencyHookService;
    private PriceTypeHook $priceTypeHookService;
    private UomHook $uomHookService;
    private StockProductService $stockProductService;
    private StoreService $storeService;
    private ImgService $imgService;



    //Add products to UDS from MS

    /**
     * @param AttributeHook $attributeHookService
     * @param CurrencyHook $currencyHookService
     * @param PriceTypeHook $priceTypeHookService
     * @param UomHook $uomHookService
     * @param StockProductService $stockProductService
     * @param StoreService $storeService
     * @param ImgService $imgService
     */
    public function __construct(
        AttributeHook $attributeHookService,
        CurrencyHook $currencyHookService,
        PriceTypeHook $priceTypeHookService,
        UomHook $uomHookService,
        StockProductService $stockProductService,
        StoreService $storeService,
        ImgService $imgService)
    {
        $this->attributeHookService = $attributeHookService;
        $this->currencyHookService = $currencyHookService;
        $this->priceTypeHookService = $priceTypeHookService;
        $this->uomHookService = $uomHookService;
        $this->stockProductService = $stockProductService;
        $this->storeService = $storeService;
        $this->imgService = $imgService;
    }

    public function insertToUds($data): array
    {
        return $this->notAddedInUds(
            $data['tokenMs'],
            $data['apiKeyUds'],
            $data['companyId'],
            $data['folder_id'],
            $data['store'],
            $data['accountId']
        );
    }

    private function getUdsCheck($companyId, $apiKeyUds,$accountId): array
    {
        set_time_limit(3600);
        $this->findNodesUds($nodeIds,$companyId,$apiKeyUds,$accountId);
        if ($nodeIds == null){
            $nodeIds = [
                "productIds" => [],
                "categoryIds" => [],
            ];
        }
        //dd($nodeIds);
        return $nodeIds;
    }

    private function getMs($folderName, $apiKeyMs){
        if ($folderName == null) {
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        } else {
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/product?filter=pathName~".$folderName;
        }
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
    }

    private function notAddedInUds($apiKeyMs,$apiKeyUds,$companyId,$folderId, $storeName,$accountId){
        $productsUds = $this->getUdsCheck($companyId,$apiKeyUds,$accountId);
        //dd($productsUds);
        $folderName = $this->getFolderNameById($folderId,$apiKeyMs);
        $storeHref = $this->storeService->getStore($storeName,$apiKeyMs)->href;
        //dd($folderName);
        set_time_limit(3600);

        if (!array_key_exists('categoryIds', $productsUds)) {
            $productsUds['categoryIds'] = [];
        }
        $this->addCategoriesToUds($productsUds["categoryIds"],$folderName,$apiKeyMs,$companyId,$apiKeyUds,$accountId);
        $productsMs = $this->getMs($folderName,$apiKeyMs);

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
                    $idNodeCategory = $this->getCategoryIdByMetaHref($productFolderHref,$apiKeyMs);
                    //dd($idNodeCategory);
                    $createdProduct = $this->createProductUds(
                        $row,$apiKeyMs,$companyId,$apiKeyUds,$storeHref,$accountId,$idNodeCategory
                    );
                    if ($createdProduct != null){
                        $this->updateProduct($createdProduct,$row->id,$apiKeyMs);
                        /*if (property_exists($row,'images')){
                            //dd($row);
                            $this->imgService->setImgUDS($createdProduct,$row->images->meta->href,$apiKeyMs,$companyId,$apiKeyUds);
                        }*/
                    }
                } else {
                    $createdProduct = $this->createProductUds($row,$apiKeyMs,$companyId,$apiKeyUds,$storeHref,$accountId);
                    if ($createdProduct != null){
                        $this->updateProduct($createdProduct,$row->id,$apiKeyMs);
                        /* if (property_exists($row,'images')){
                             //dd($row);
                             $this->imgService->setImgUDS($createdProduct,$row->images->meta->href,$apiKeyMs,$companyId,$apiKeyUds);
                         }*/
                    }
                }
            }

        }

        return [
            "message" => "Successful export products to UDS"
        ];
    }

    private function addCategoriesToUds($check, $pathName, $apiKeyMs, $companyId, $apiKeyUds,$accountId, $nodeId = ""){
        $categoriesMs = null;
        if (!$this->getCategoriesMs($categoriesMs,$pathName,$apiKeyMs)) return;

        foreach ($categoriesMs as $categoryMs){
            $nameCategory = $categoryMs->name;
            if (!in_array($categoryMs->externalCode,$check)){
                if ($nodeId == ""){
                    $createdCategory = $this->createCategoryUds($categoryMs->name,$companyId,$apiKeyUds,$accountId);
                } else {
                    $folderHref = $categoryMs->productFolder->meta->href;
                    $idNodeCategory = $this->getCategoryIdByMetaHref($folderHref,$apiKeyMs);
                    //dd($idNodeCategory);
                    $createdCategory = $this->createCategoryUds(
                        $nameCategory,
                        $companyId,
                        $apiKeyUds,
                        $accountId,
                        $idNodeCategory);
                }
                //dd($newPath);
                if ($createdCategory != null){
                    $createdCategoryId = $createdCategory->id;
                    $newNodeId = "".$createdCategoryId;
                    $check[] = "".$createdCategoryId;
                    $this->updateCategory($createdCategoryId, $categoryMs->id,$apiKeyMs);
                } else {
                    $newNodeId = $categoryMs->externalCode;
                }

            } else {
                $newNodeId = $categoryMs->externalCode;
            }

            $newPath = $pathName."/".$nameCategory;
            $this->addCategoriesToUds(
                $check,
                $newPath,
                $apiKeyMs,
                $companyId,
                $apiKeyUds,
                $accountId,
                $newNodeId
            );
        }
    }

    private function getFolderNameById($folderId, $apiKeyMs)
    {
        if ($folderId == 0){
            $result = null;
        } else {
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$folderId;
            $client = new MsClient($apiKeyMs);
            $result = $client->get($url)->name;
        }
        return $result;
    }

    private function getCategoriesMs(&$rows,$folderName,$apiKeyMs): bool
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName=".$folderName;
        $client = new MsClient($apiKeyMs);
        try {
            $json = $client->get($url);
        }catch (ClientException $e){
            dd($url,$e->getMessage());
        }
        $rows = $json->rows;
        return ($json->meta->size > 0 );
    }

    private function updateCategory($createdCategoryId,$idMs,$apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$idMs;
        $client = new MsClient($apiKeyMs);
        $body = [
            "externalCode" => "".$createdCategoryId,
        ];
        $client->put($url,$body);
    }

    private function updateProduct($createdProduct, $idMs, $apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product/".$idMs;
        $client = new MsClient($apiKeyMs);

        //dd($createdProduct);

        $body = [
            "attributes" => [
                0 => [
                    "meta" => $this->attributeHookService->getProductAttribute("id (UDS)",$apiKeyMs),
                    "name" => "id (UDS)",
                    "value" => "".$createdProduct->id,
                ],
            ],
        ];

        $nameOumUds = $createdProduct->data->measurement;

        if ($nameOumUds != "PIECE"){
            if ($createdProduct->data->offer == null){
                $priceDefault = $createdProduct->data->price;

                if ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE"){
                    $priceDefault /= 1000.0;
                } elseif ($nameOumUds == "METRE"){
                    $priceDefault /= 100.0;
                }

                $body["attributes"][1]= [
                    "meta" => $this->attributeHookService->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $priceDefault,
                ];
            }
            else {
                $offerPrice = $createdProduct->data->offer->offerPrice;
                if ($createdProduct->data->increment != null && $createdProduct->data->minQuantity != null){
                    if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                        // offer price 1000
                        $offerPrice /= 1000.0;
                    } elseif($nameOumUds == "CENTIMETRE"){
                        //offer price 100
                        $offerPrice /= 100.0;
                    }
                }
                elseif($createdProduct->data->increment == null && $createdProduct->data->minQuantity == null) {
                    if ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE"){
                        $offerPrice /= 1000.0;
                    } elseif ($nameOumUds == "METRE"){
                        $offerPrice /= 100.0;
                    }
                }
                $body["attributes"][1]= [
                    "meta" => $this->attributeHookService->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $offerPrice,
                ];
            }
        }

        $client->put($url,$body);
    }

    private function createProductUds($product,$apiKeyMs,$companyId,$apiKeyUds,$storeHref,$accountId,$nodeId = 0){
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);
        $error_log = "Не удалось создать товар ".$product->name." в UDS.";

        $prices = [];

        foreach ($product->salePrices as $price){
            if ($price->priceType->name == "Цена продажи"){
                $prices["salePrice"] = ($price->value / 100);
            } elseif ($price->priceType->name == "Акционный"){
                $prices["offerPrice"] = ($price->value / 100);
            }
        }

        if ($prices["salePrice"] <= 0){
            $bd = new BDController();
            $bd->errorProductLog($accountId, $error_log." Не была указана цена товара в MS");
            return null;
        }

        $nameOumUds = $this->getUomUdsByMs($product->uom->meta->href,$apiKeyMs);
        if ($nameOumUds == ""){
            $bd = new BDController();
            $bd->errorProductLog($accountId,$error_log." Была указана некорректная ед.изм товара в MS");
            return null;
        }

        if (strlen($product->name) > 100){
            $name = mb_substr($product->name,0,100);
        } else {
            $name = $product->name;
        }

        $body = [
            "name" => $name,
            "data" => [
                "type" => "ITEM",
                "price" => $prices["salePrice"],
                "measurement" => $nameOumUds,
            ],
        ];

        if (property_exists($product,"attributes")){

            $isFractionProduct = false;
            $isOfferProduct = false;

            foreach ($product->attributes as $attribute){
                if ($attribute->name == "Дробное значение товара (UDS)" && $attribute->value == 1){
                    $isFractionProduct = true;
                    //break;
                } elseif ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1){
                    $isOfferProduct = true;
                }
            }

            if ($isFractionProduct && (
                    $nameOumUds == "KILOGRAM"
                    || $nameOumUds == "LITRE"
                    || $nameOumUds == "METRE")
            ){
                $bd = new BDController();
                $bd->errorProductLog($accountId,$error_log." Выбранная ед.изм товара в MS, не может быть дробным товаром в UDS.");
                return null;
            }

            if (
                $isOfferProduct &&
                ($prices['offerPrice'] <= 0 || $prices['offerPrice'] > $prices['salePrice'])
            ){
                $bd = new BDController();
                $bd->errorProductLog($accountId,$error_log." Акционная цена не может быть равна 0, также не может быть больше Цены продажи");
                return null;
            }

            foreach ($product->attributes as $attribute){
                if ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1){
                $body["data"]["offer"]["offerPrice"] = $prices["offerPrice"];
            }
            elseif ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value == 1){
                $body["data"]["offer"]["skipLoyalty"] = true;
            }
            elseif ($attribute->name == "Шаг дробного значения (UDS)" && $isFractionProduct){
                //if ($attribute->value <= 0 || $attribute->value == null) return null;
                    $body["data"]["increment"] = intval($attribute->value);
                if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                    $body["data"]["increment"] *= 1000.0;
                    if ($body["data"]["increment"] >= 10000000){
                        //dd($body["data"]["increment"]);
                        $bd = new BDController();
                        $bd->errorProductLog($accountId,$error_log." Шаг дробного значения (UDS) введен некорректно");
                        return null;
                    }
                } elseif ($nameOumUds == "CENTIMETRE"){
                    $body["data"]["increment"] *= 100.0;
                    if ($body["data"]["increment"] >= 1000000){
                        //dd($body["data"]["increment"]);
                        $bd = new BDController();
                        $bd->errorProductLog($accountId,$error_log." Шаг дробного значения (UDS) введен некорректно");
                        return null;
                    }
                }
            }
            elseif ($attribute->name == "Минимальный размер заказа дробного товара (UDS)" && $isFractionProduct){
                //if ($attribute->value <= 0 || $attribute->value == null) return null;
                $body["data"]["minQuantity"] = intval($attribute->value);
                if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                    $body["data"]["price"] /= 1000;
                    $body["data"]["minQuantity"] *= 1000.0;
                    if ($body["data"]["minQuantity"] >= 10000000){
                        $bd = new BDController();
                        $bd->errorProductLog($accountId,$error_log." Минимальный размер заказа дробного товара (UDS) введен некорректно");
                        return null;
                    }
                } elseif ($nameOumUds == "CENTIMETRE"){
                    $body["data"]["price"] /= 100;
                    $body["data"]["minQuantity"] *= 100.0;
                    if ($body["data"]["minQuantity"] >= 1000000){
                        $bd = new BDController();
                        $bd->errorProductLog($accountId,$error_log." Минимальный размер заказа дробного товара (UDS) введен некорректно");
                        return null;
                    }
                }
            }
            elseif ($attribute->name == "Товар неограничен (UDS)"){
                if ($attribute->value == 1){
                    $stock = null;
                } else {
                    $stock = $this->stockProductService->getProductStockMs(
                        $product->externalCode,
                        $storeHref,
                        $apiKeyMs
                    );
                }
                $body["data"]["inventory"]["inStock"] = $stock;
                }
            }

            if (!array_key_exists("inventory",$body["data"])){
                $body["data"]["inventory"]["inStock"] = $this->stockProductService
                    ->getProductStockMs($product->externalCode,
                        $storeHref,
                        $apiKeyMs
                    );
            }

            if (
                $isFractionProduct
                && (
                    !array_key_exists("increment",$body["data"])
                    || !array_key_exists("minQuantity", $body["data"])
                )
            ){
                //dd(($body));
                $bd = new BDController();
                $bd->errorProductLog($accountId,$error_log." У дробного товара не введено Минимальный размер заказа или Шаг дробного значения");
                return null;
            }
            if($isFractionProduct) {
                if ($body["data"]["minQuantity"] < $body["data"]["increment"]){
                    $bd = new BDController();
                    $bd->errorProductLog($accountId,$error_log." У дробного товара Шаг дробного значения, не может быть больше Минимального размера заказа");
                    return null;
                }
            }

            if ($isFractionProduct){
                //if ($body["name"] == "Мешок с негром"){
                $dPrice = explode('.',"".$body["data"]["price"]);
                //dd($dPrice);
                if (count($dPrice) > 1 && strlen($dPrice[1]) > 2){
                    $bd = new BDController();
                    $bd->errorProductLog($accountId,$error_log." У товара цена имеет 3 числа после запятой (дробная часть)");
                    return null;
                }
                // }
            }

            if ($nameOumUds == "PIECE"){
                $body["data"]["minQuantity"] = null;
                $body["data"]["increment"] = null;
            }

        }

        if (property_exists($product, "article")){
            $body["data"]["sku"] = $product->article;
        }

        if ($nodeId > 0){
            $body["nodeId"] = intval($nodeId);
        }

        //dd(($body));

        if (property_exists($product,'images')){
            //dd($product);
            $imgIds = $this->imgService->setImgUDS($product->images->meta->href,$apiKeyMs,$companyId,$apiKeyUds);
            $body["data"]["photos"] = $imgIds;
            dd($body);
        }

        try {
            return $client->post($url,$body);
        }catch (ClientException $e){
            //dd(json_encode($body),$e->getMessage());
            $bd = new BDController();
            $bd->errorProductLog($accountId,$e->getMessage());
            return null;
        }
    }

    private function createCategoryUds($nameCategory,$companyId,$apiKeyUds, $accountId ,$nodeId = ""){
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);
        $body = [
            "name" => $nameCategory,
            "data" => [
                "type" => "CATEGORY",
            ],
        ];
        if (intval($nodeId) > 0 || $nodeId != ""){
            $body["nodeId"] = intval($nodeId);
            // dd($body);
        }
        try {
            return $client->post($url, $body);
        }catch (ClientException $e){
            $bd = new BDController();
            $bd->errorProductLog($accountId,$e->getMessage());
            return null;
        }
    }

    private function haveRowsInResponse(&$url,$offset,$companyId,$apiKeyUds, $accountId ,$nodeId=0): bool
    {
        $url = "https://api.uds.app/partner/v2/goods?max=50&offset=".$offset;
        if ($nodeId > 0){
            $url = $url."&nodeId=".$nodeId;
        }
        $client = new UdsClient($companyId,$apiKeyUds);
        try {
            $json = $client->get($url);
            return count($json->rows) > 0;
        }catch (ClientException $e){
            $bd = new BDController();
            $bd->errorProductLog($accountId,$e->getMessage());
            return false;
        }
    }

    private function findNodesUds(&$result,$companyId, $apiKeyUds, $accountId,$nodeId = 0, $path=""): void
    {
//        if ($nodeId > 0 ){
//            $url = "https://api.uds.app/partner/v2/goods?nodeId=".$nodeId;
//        }
//        else {
//            $url = "https://api.uds.app/partner/v2/goods";
//        }

//        $client = new UdsClient($companyId,$apiKeyUds);
//        $json = $client->get($url);
//
//        if (count($json->rows) == 0) {
//            return;
//        }
        $offset = 0;
        while ($this->haveRowsInResponse($url,$offset,$companyId,$apiKeyUds,$accountId,$nodeId)){
            $client = new UdsClient($companyId,$apiKeyUds);
            $json = $client->get($url);
            foreach ($json->rows as $row) {
                $currId = "".$row->id;
                if ($row->data->type == "ITEM" || $row->data->type == "VARYING_ITEM"){
                    $result["productIds"][] = $currId;
                }
                elseif ($row->data->type == "CATEGORY"){
                    $result["categoryIds"][] = $currId;
                    $newPath = $path."/".$row->name;
                    $this->findNodesUds($result,$companyId,$apiKeyUds,$accountId,$currId,$newPath);
                }
            }
            $offset += 50;
        }

    }

    private function getCategoryIdByMetaHref($href, $apiKeyMs){
        $client = new MsClient($apiKeyMs);
        return $client->get($href)->externalCode;
    }

    private function getUomUdsByMs($href, $apiKeyMs): string
    {
        $client = new MsClient($apiKeyMs);
        $json = $client->get($href);

        return match ($json->name) {
            "шт" => "PIECE",
            "см" => "CENTIMETRE",
            "м" => "METRE",
            "мм" => "MILLILITRE",
            "л; дм3" => "LITRE",
            "г" => "GRAM",
            "кг" => "KILOGRAM",
            default => "",
        };
    }

}
