<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
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

    //Add products to UDS from MS
    public function insertToUds($data)
    {
        return $this->notAddedInUds(
            $data['tokenMs'],
            $data['apiKeyUds'],
            $data['companyId']
        );
    }

    private function getUdsCheck($companyId, $apiKeyUds){
        $this->findNodesUds($nodeIds,$companyId,$apiKeyUds);
        return $nodeIds;
    }

    private function getMs($apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product";
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
    }

    private function notAddedInUds($apiKeyMs,$apiKeyUds,$companyId){
        $productsUds = $this->getUdsCheck($companyId,$apiKeyUds);
        //dd($productsUds);
        $categoriesMs = $this->getCategoriesMs($apiKeyMs);

        foreach ($categoriesMs->rows as $categoryMs){
            if ($categoryMs->pathName == "")
                if (!in_array($categoryMs->externalCode,$productsUds["categoryIds"])){
                    //dd($categoryMs->externalCode,$productsUds["categoryIds"]);
                    //echo $categoryMs->externalCode."\r\n";
                    $createdCategoryId = $this->createCategoryUds($categoryMs->name,$companyId,$apiKeyUds)->id;
                    $productsUds["categoryIds"][] = "".$createdCategoryId;
                    $this->updateCategory($createdCategoryId, $categoryMs->id,$apiKeyMs);
                }
        }

        foreach ($categoriesMs->rows as $categoryMs){
            if ($categoryMs->pathName != "" && !str_contains($categoryMs->pathName, "/"))
                if (!in_array($categoryMs->externalCode,$productsUds["categoryIds"])){
                    //dd($categoryMs->externalCode,$productsUds["categoryIds"]);
                    //echo $categoryMs->externalCode."\r\n";
                    $folderHref = $categoryMs->productFolder->meta->href;
                    $idNodeCategory = $this->getCategoryIdByMetaHref($folderHref,$apiKeyMs);
                    $createdCategoryId = $this->createCategoryUds(
                        $categoryMs->name,
                        $companyId,
                        $apiKeyUds,
                        $idNodeCategory)->id;
                    $productsUds["categoryIds"][] = "".$createdCategoryId;
                    $this->updateCategory($createdCategoryId, $categoryMs->id,$apiKeyMs);
                }
        }

        foreach ($categoriesMs->rows as $categoryMs){
            if (
                $categoryMs->pathName != ""
                && str_contains($categoryMs->pathName, "/")
                && count(explode('/',$categoryMs->pathName)) == 2
            )
                if (!in_array($categoryMs->externalCode,$productsUds["categoryIds"])){
                    //dd($categoryMs->externalCode,$productsUds["categoryIds"]);
                    //echo $categoryMs->externalCode."\r\n";
                    $folderHref = $categoryMs->productFolder->meta->href;
                    $idNodeCategory = $this->getCategoryIdByMetaHref($folderHref,$apiKeyMs);
                    $createdCategoryId = $this->createCategoryUds(
                        $categoryMs->name,
                        $companyId,
                        $apiKeyUds,
                        $idNodeCategory)->id;
                    $productsUds["categoryIds"][] = "".$createdCategoryId;
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
                    $idNodeCategory = $this->getCategoryIdByMetaHref($productFolderHref,$apiKeyMs);
                    //dd($idNodeCategory);
                    $createdProduct = $this->createProductUds(
                        $row,$apiKeyMs,$companyId,$apiKeyUds,$idNodeCategory
                    );
                    if ($createdProduct != null)
                        $this->updateProduct($createdProduct,$row->id,$apiKeyMs);
                } else {
                    $createdProduct = $this->createProductUds($row,$apiKeyMs,$companyId,$apiKeyUds);
                    if ($createdProduct != null)
                        $this->updateProduct($createdProduct,$row->id,$apiKeyMs);
                }
            }

        }

        return [
            "message" => "Successful export products to UDS"
        ];
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

        if ($createdProduct->data->offer == null){
            $priceDefault = $createdProduct->data->price;

            if ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE"){
                $priceDefault /= 1000.0;
            } elseif ($nameOumUds == ""){
                $priceDefault /= 100.0;
            }

            $body["attributes"][1]= [
                "meta" => $this->attributeHookService->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Цена минимального размера заказа дробного товара (UDS)",
                "value" => $priceDefault,
            ];
        } else {
            $offerPrice = $createdProduct->data->offer->offerPrice;
            if ($createdProduct->data->increment != null || $createdProduct->data->minQuantity != null){
                if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                    // offer price 1000
                    $offerPrice /= 1000.0;
                } elseif($nameOumUds == "CENTIMETRE"){
                    //offer price 100
                    $offerPrice /= 100.0;
                }
            }
            $body["attributes"][1]= [
                "meta" => $this->attributeHookService->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Цена минимального размера заказа дробного товара (UDS)",
                "value" => $offerPrice,
            ];
        }

        $client->put($url,$body);
    }

    private function createProductUds($product,$apiKeyMs,$companyId,$apiKeyUds,$nodeId = 0){
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId,$apiKeyUds);

        $prices = [];

        foreach ($product->salePrices as $price){
            if ($price->priceType->name == "Цена продажи"){
                $prices["salePrice"] = ($price->value / 100);
            } elseif ($price->priceType->name == "Акционный"){
                $prices["offerPrice"] = ($price->value / 100);
            }
        }

        if ($prices["salePrice"] <= 0){
            return null;
        }

        $nameOumUds = $this->getUomUdsByMs($product->uom->meta->href,$apiKeyMs);
        $body = [
            "name" => $product->name,
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
                return null;
            }

            if (
                $isOfferProduct &&
                ($prices['offerPrice'] <= 0 || $prices['offerPrice'] > $prices['salePrice'])
            ){
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
                    if ($attribute->value <= 0 || $attribute->value == null) return null;
                    $body["data"]["increment"] = intval($attribute->value);
                    if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                        $body["data"]["increment"] *= 1000.0;
                        if ($body["data"]["increment"] >= 10000000){
                            //dd($body["data"]["increment"]);
                            return null;
                        }
                    } elseif ($nameOumUds == "CENTIMETRE"){
                        $body["data"]["increment"] *= 100.0;
                        if ($body["data"]["increment"] >= 1000000){
                            //dd($body["data"]["increment"]);
                            return null;
                        }
                    }
                }
                elseif ($attribute->name == "Минимальный размер заказа дробного товара (UDS)" && $isFractionProduct){
                    if ($attribute->value <= 0 || $attribute->value == null) return null;
                    $body["data"]["minQuantity"] = intval($attribute->value);
                    if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                        $body["data"]["price"] /= 1000;
                        $body["data"]["minQuantity"] *= 1000.0;
                        if ($body["data"]["minQuantity"] >= 10000000){
                            return null;
                        }
                    } elseif ($nameOumUds == "CENTIMETRE"){
                        $body["data"]["price"] /= 100;
                        $body["data"]["minQuantity"] *= 100.0;
                        if ($body["data"]["minQuantity"] >= 1000000){
                            return null;
                        }
                    }
                }
                elseif ($attribute->name == "Товар неограничен (UDS)" && $attribute->value == 1){
                    $body["data"]["inventory"]["inStock"] = null;
                }
            }

            if (
                $isFractionProduct
                && (
                    !array_key_exists("increment",$body["data"])
                    || !array_key_exists("minQuantity", $body["data"])
                )
            ){
                //dd(($body));
                return null;
            }
            if($isFractionProduct) {
                if ($body["data"]["minQuantity"] < $body["data"]["increment"]){
                    return null;
                }
            }

            if ($isFractionProduct){
                //if ($body["name"] == "Мешок с негром"){
                $dPrice = explode('.',"".$body["data"]["price"]);
                //dd($dPrice);
                if (count($dPrice) > 1 && strlen($dPrice[1]) > 2){
                    return null;
                }
                // }
            }

        }

        if (property_exists($product, "article")){
            $body["data"]["sku"] = $product->article;
        }

        if ($nodeId > 0){
            $body["nodeId"] = intval($nodeId);
        }

        //dd(($body));

        try {
            return $client->post($url,$body);
        } catch (ClientException $e){
            dd($body,$e->getMessage());
        }

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
            $body["nodeId"] = intval($nodeId);
            // dd($body);
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

    private function getCategoryIdByMetaHref($href, $apiKeyMs){
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
