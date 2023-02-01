<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\mainURL;
use App\Services\AdditionalServices\ImgService;
use App\Services\AdditionalServices\StockProductService;
use App\Services\MetaServices\Entity\StoreService;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class ProductUpdateUdsService
{

    private AttributeHook $attributeHookService;
    private StockProductService $stockProductService;
    private StoreService $storeService;
    private ImgService $imgService;

    /**
     * @param AttributeHook $attributeHookService
     * @param StockProductService $stockProductService
     * @param StoreService $storeService
     * @param ImgService $imgService
     */
    public function __construct(AttributeHook $attributeHookService, StockProductService $stockProductService, StoreService $storeService, ImgService $imgService)
    {
        $this->attributeHookService = $attributeHookService;
        $this->stockProductService = $stockProductService;
        $this->storeService = $storeService;
        $this->imgService = $imgService;
    }


    public function updateProductsUds($data){
        $apiKeyMs = $data['tokenMs'];
        $companyId = $data['companyId'];
        $apiKeyUds = $data['apiKeyUds'];
        $folderId = $data['folder_id'];
        $storeName = $data['store'];
        $accountId = $data['accountId'];

        set_time_limit(600);

        $storeHref = $this->storeService->getStore($storeName,$apiKeyMs)->href;
        $folderName = $this->getFolderNameById($folderId,$apiKeyMs);
        $msProducts = $this->getMs($folderName,$apiKeyMs);

        foreach ($msProducts->rows as $row){
             try {
                $productId = null;
                if (property_exists($row, 'attributes')){
                    foreach ($row->attributes as $attribute){
                        if ($attribute->name == "id (UDS)"){
                            $productId = $attribute->value;
                            break;
                        } else continue;
                    }
                }
                if ($productId != null){
                    if (property_exists($row,"productFolder")){
                        $productFolderHref = $row->productFolder->meta->href;
                        $idNodeCategory = $this->getCategoryIdByMetaHref($productFolderHref,$apiKeyMs);
                        $updatedProduct = $this->updateProductInUds($row,$storeHref,$productId,$apiKeyMs,$companyId, $apiKeyUds,$accountId,$idNodeCategory);
                    }
                    else {
                        $updatedProduct = $this->updateProductInUds($row,$storeHref,$productId,$apiKeyMs,$companyId, $apiKeyUds,$accountId);
                    }
                    if ($updatedProduct != null){
                        $this->updateProduct($updatedProduct,$row->id,$apiKeyMs);
                    }
                } else continue;
            } catch (BadResponseException $e){
                continue;
            }

        }
        return [
            'message' => 'Updated products in UDS'
        ];

    }

    private function updateProductInUds(mixed $msProduct,string $storeHref,string $goodId, string $apiKeyMs, string $companyId, string $apiKeyUds, string $accountId, $nodeId = 0)
    {
        $url = "https://api.uds.app/partner/v2/goods/".$goodId;
        $mainUrl = new mainURL();
        $client = new UdsClient($companyId,$apiKeyUds);
        $Client_UDS = new UdsClient($companyId,$apiKeyUds);
        $Client_MS = new MsClient($apiKeyMs);
        $error_log = "Не удалось обновить товар ".$msProduct->name." в UDS.";
        $bd = new BDController();

        try {
            $body_json_by_UDS = $client->get($url);
            $prices = [];
            if ($msProduct->variantsCount > 0){
                if (strlen($msProduct->name) > 100){ $name = mb_substr($msProduct->name,0,100); } else { $name = $msProduct->name; }

                $body = [
                    "name" => $name,
                    "data" => [
                        "type" => "VARYING_ITEM",
                        "description" => "",
                        "photos" => [],
                        "variants" => [],
                    ],
                ];

                $variant = $Client_MS->get($mainUrl->url_ms().'variant?filter=productid='.$msProduct->id)->rows;
                foreach ($variant as $id=>$item){
                    $price = null;
                    $variants[$id] = [
                        'name' => $item->name,
                        'sku' => null,
                        'price' => null,
                        'offer' => [
                            'offerPrice' => null,
                            'skipLoyalty' => false,
                        ],
                        'inventory' => [
                            'inStock' => null,
                        ],
                    ];
                    foreach ($item->salePrices as $item_price){
                        if ($item_price->value > 0){ $variants[$id]['price'] = $item_price->value / 100; break;

                        } else { continue; }
                    }
                    if ($variants[$id]['price'] < 0 or $variants[$id]['price'] == null) { unset($variants[$id]); continue; }
                    if (property_exists($msProduct,"attributes")){
                        foreach ($msProduct->attributes as $attribute)
                        {
                            if ($attribute->name == "Акционный товар (UDS)" && $attribute->value == true){
                                foreach ($item->salePrices as $salePrices){
                                    if ($salePrices->priceType->name == "Акционный"){
                                        $variants[$id]['offer']['offerPrice'] = $salePrices->value / 100;
                                    }
                                }
                            }
                            if ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value == true){
                                $variants[$id]['offer']['skipLoyalty'] = $attribute->value;
                            }
                            if ($attribute->name == "Товар неограничен (UDS)" && $attribute->value == false){
                                $inStock = $Client_MS->get("https://online.moysklad.ru/api/remap/1.2/report/stock/all?"."filter=store=".$storeHref.";search=".$item->name)->rows;
                                if ($inStock) { $variants[$id]['inventory']['inStock'] = $inStock[0]->quantity; }
                            }
                        }
                    }
                }

                if ($variants){
                    $body['data']['variants'] = $variants;
                }else { return null; }
                if ($nodeId > 0){ $body["nodeId"] = intval($nodeId); }

                if (property_exists($msProduct,'images')){
                    if (property_exists($body_json_by_UDS, 'imageUrls')){
                        if (count($body_json_by_UDS->imageUrls) <= 0) {
                            $imgIds = $this->imgService->setImgUDS($msProduct->images->meta->href, $apiKeyMs, $companyId, $apiKeyUds);
                            $body["data"]["photos"] = $imgIds;
                        }
                    }
                }

            }
            else {

                foreach ($msProduct->salePrices as $price){
                    if ($price->priceType->name == "Цена продажи"){ $prices["salePrice"] = ($price->value / 100);
                    } else {
                        if ($price->priceType->name == "Акционный") $prices["offerPrice"] = ($price->value / 100);
                    }
                }
                if ($prices == []){
                    $prices["salePrice"] = 0;
                    for ($index = 0; $index < count($msProduct->salePrices); $index++){
                        if ($prices["salePrice"] > 0) break;
                        else $prices["salePrice"] = $msProduct->salePrices[$index]->value / 100;
                    }
                }
                if ($prices["salePrice"] <= 0){
                    $bd->errorProductLog($accountId, $error_log." Не была указана цена товара в MS");
                    return null;
                }

                //ДО делать get UoM
                $nameOumUds = $this->getUomUdsByMs($msProduct->uom->meta->href,$apiKeyMs);
                if ($nameOumUds == ""){
                    $bd->errorProductLog($accountId,$error_log." Была указана некорректная ед.изм товара в MS");
                    return null;
                }
                if (strlen($msProduct->name) > 100){ $name = mb_substr($msProduct->name,0,100); } else { $name = $msProduct->name; }
                if (property_exists($msProduct, 'description')) {
                    $description = $msProduct->description;
                } else $description = "";
                $body = [
                    "name" => $name,
                    "data" => [
                        "type" => "ITEM",
                        "price" => $prices["salePrice"],
                        "measurement" => $nameOumUds,
                        "description" => $description,
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
                        $bd = new BDController();
                        $bd->errorProductLog($accountId,$error_log." Выбранная ед.изм товара в MS, не может быть дробным товаром в UDS.");
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
                            }else {
                                $stock = $this->stockProductService->getProductStockMs(
                                    $msProduct->externalCode,$storeHref,$apiKeyMs
                                );
                            }
                            $body["data"]["inventory"]["inStock"] = $stock;
                        }
                    }

                    if (!array_key_exists("inventory",$body["data"])){
                        $body["data"]["inventory"]["inStock"] = $this->stockProductService
                            ->getProductStockMs($msProduct->externalCode,
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
                if (property_exists($msProduct, "article")){
                    $body["data"]["sku"] = $msProduct->article;
                }
                if ($nodeId > 0){
                    $body["nodeId"] = intval($nodeId);
                }
                if (property_exists($msProduct,"images")){
                    if (property_exists($body_json_by_UDS, 'imageUrls')){
                        if (count($body_json_by_UDS->imageUrls) <= 0) {
                            $imgIds = $this->imgService->setImgUDS($msProduct->images->meta->href, $apiKeyMs, $companyId, $apiKeyUds);
                            $body["data"]["photos"] = $imgIds;
                        }
                    }
                }

            }

        } catch (\Throwable $e){
            return null;
        }

        try {
            return $client->put($url,$body);
        }catch (ClientException $e){
            $bd = new BDController();
            $bd->errorProductLog($accountId,$e->getMessage());
            return null;
        }
    }

    private function getMs($folderName,$apiKeyMs){
        $client = new MsClient($apiKeyMs);
        $attributes = $client->get('https://online.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes')->rows;
        foreach ($attributes as $item){
            if ($item->name == 'id (UDS)') {
                $attributes = $item->meta->href;
            } else continue;
        }
        if ($folderName == null) {
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/product?filter=".$attributes.'!=';
        } else {
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/product?filter=pathName~".$folderName;
        }
        return $client->get($url);
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
        if ($folderId == 0){
            $result = null;
        } else {
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$folderId;
            $client = new MsClient($apiKeyMs);
            $result = $client->get($url)->name;
        }
        return $result;
    }

}
