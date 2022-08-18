<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\ClientException;

class ProductUpdateUdsService
{

    private AttributeHook $attributeHookService;

    /**
     * @param AttributeHook $attributeHookService
     */
    public function __construct(AttributeHook $attributeHookService)
    {
        $this->attributeHookService = $attributeHookService;
    }


    public function updateProductsUds($data){
        $apiKeyMs = $data['tokenMs'];
        $companyId = $data['companyId'];
        $apiKeyUds = $data['apiKeyUds'];
        $folderId = $data['folder_id'];

        $folderName = $this->getFolderNameById($folderId,$apiKeyMs);

        set_time_limit(3600);

        $msProducts = $this->getMs($folderName,$apiKeyMs);

        foreach ($msProducts->rows as $row){
            $productId = null;
            if (property_exists($row, 'attributes')){
                foreach ($row->attributes as $attribute){
                    if ($attribute->name == "id (UDS)"){
                        $productId = $attribute->value;
                        break;
                    }
                }
            }

            if ($productId != null){
                if (property_exists($row,"productFolder")){
                    $productFolderHref = $row->productFolder->meta->href;
                    $idNodeCategory = $this->getCategoryIdByMetaHref($productFolderHref,$apiKeyMs);
                    $updatedProduct = $this->updateProductInUds($row,$productId,$apiKeyMs,$companyId, $apiKeyUds,$idNodeCategory);
                    if ($updatedProduct != null){
                        $this->updateProduct($updatedProduct,$row->id,$apiKeyMs);
                    }
                }
                else {
                    $updatedProduct = $this->updateProductInUds($row,$productId,$apiKeyMs,$companyId, $apiKeyUds);
                    if ($updatedProduct != null){
                        $this->updateProduct($updatedProduct,$row->id,$apiKeyMs);
                    }
                }
            }

        }

        return [
            'message' => 'Updated products in UDS'
        ];

    }

    private function updateProductInUds($msProduct, $goodId, $apiKeyMs, $companyId, $apiKeyUds, $nodeId = 0)
    {
        $url = "https://api.uds.app/partner/v2/goods/".$goodId;
        $client = new UdsClient($companyId,$apiKeyUds);

        $json = $client->get($url);

        if ($json->data->type != "ITEM"){
            return null;
        }

        $prices = [];

        foreach ($msProduct->salePrices as $price){
            if ($price->priceType->name == "Цена продажи"){
                $prices["salePrice"] = ($price->value / 100);
            }
        }

        if ($prices["salePrice"] <= 0){
            return null;
        }

        $nameOumUds = $this->getUomUdsByMs($msProduct->uom->meta->href,$apiKeyMs);
        if ($nameOumUds == "") return null;

        if (strlen($msProduct->name) > 100){
            $name = mb_substr($msProduct->name,0,100);
        } else {
            $name = $msProduct->name;
        }

        $body = [
            "name" => $name,
            "data" => [
                "type" => "ITEM",
                "price" => $prices["salePrice"],
                "measurement" => $nameOumUds,
            ],
        ];

        if (property_exists($msProduct,"attributes")){

            $isFractionProduct = false;

            foreach ($msProduct->attributes as $attribute){
                if ($attribute->name == "Дробное значение товара (UDS)"){
                    if ($attribute->value == 1){
                        $isFractionProduct = true;
                    }
                    else {
                        $body["data"]["minQuantity"] = null;
                        $body["data"]["increment"] = null;
                    }
                    //break;
                }
            }

            if ($isFractionProduct && (
                $nameOumUds == "KILOGRAM"
                    || $nameOumUds == "LITRE"
                || $nameOumUds == "METRE")
            ){
                return null;
            }

            foreach ($msProduct->attributes as $attribute){
                if ($attribute->name == "Шаг дробного значения (UDS)" && $isFractionProduct){
                    //if ($attribute->value <= 0 || $attribute->value == null) return null;
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
                    //if ($attribute->value <= 0 || $attribute->value == null) return null;
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

            if ($nameOumUds == "PIECE"){
                $body["data"]["minQuantity"] = null;
                $body["data"]["increment"] = null;
            }

        }

        /*if ($isFractionProduct){
            $body["data"]["measurement"] = $nameOumUds;
        }*/

        if (property_exists($msProduct, "article")){
            $body["data"]["sku"] = $msProduct->article;
        }

        if ($nodeId > 0){
            $body["nodeId"] = intval($nodeId);
        }

        //if ($body["name"] == "Мешок с негром")
        //dd($body);
        try {
            return $client->put($url,$body);
        } catch (ClientException $e){
            dd(json_encode($body),$e->getMessage());
        }

    }

    private function getMs($folderName,$apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product?filter=pathName~".$folderName;
        $client = new MsClient($apiKeyMs);
        return $client->get($url);
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

    private function updateProduct($updatedProduct, $idMs, $apiKeyMs){
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product/".$idMs;
        $client = new MsClient($apiKeyMs);

        //dd($createdProduct);

        $body = [
            "attributes" => [
                0 => [
                    "meta" => $this->attributeHookService->getProductAttribute("id (UDS)",$apiKeyMs),
                    "name" => "id (UDS)",
                    "value" => "".$updatedProduct->id,
                ],
            ],
        ];

        $nameOumUds = $updatedProduct->data->measurement;

        if ($nameOumUds != "PIECE") {
            if ($updatedProduct->data->offer == null){
                $priceDefault = $updatedProduct->data->price;

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
                $offerPrice = $updatedProduct->data->offer->offerPrice;
                if ($updatedProduct->data->increment != null && $updatedProduct->data->minQuantity != null){
                    if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM"){
                        // offer price 1000
                        $offerPrice /= 1000.0;
                    } elseif($nameOumUds == "CENTIMETRE"){
                        //offer price 100
                        $offerPrice /= 100.0;
                    }
                }
                elseif($updatedProduct->data->increment == null && $updatedProduct->data->minQuantity == null) {
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

    private function getCategoryIdByMetaHref($href, $apiKeyMs){
        $client = new MsClient($apiKeyMs);
        return $client->get($href)->externalCode;
    }

    private function getFolderNameById($folderId, $apiKeyMs)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$folderId;
        $client = new MsClient($apiKeyMs);
        return $client->get($url)->name;
    }

}
