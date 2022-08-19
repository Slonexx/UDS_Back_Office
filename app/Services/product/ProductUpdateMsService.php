<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Services\MetaServices\MetaHook\AttributeHook;
use App\Services\MetaServices\MetaHook\CurrencyHook;
use App\Services\MetaServices\MetaHook\PriceTypeHook;
use App\Services\MetaServices\MetaHook\UomHook;
use GuzzleHttp\Exception\ClientException;

class ProductUpdateMsService
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


    public function updateProductsMs($data){
        $apiKeyMs = $data['tokenMs'];
        $companyId = $data['companyId'];
        $apiKeyUds = $data['apiKeyUds'];

        set_time_limit(3600);

        $hrefAttrib = $this->attributeHookService->getProductAttribute("id (UDS)",$apiKeyMs)->href;
        $this->findNodesUds($apiKeyMs,$companyId,$apiKeyUds,$hrefAttrib);

        return [
            "message" => "Updated products in MS"
        ];
    }

    private function findNodesUds($apiKeyMs, $companyId, $apiKeyUds, $hrefMsAttribProduct, $nodeId = 0): void
    {
        if ($nodeId > 0 ){
            $url = "https://api.uds.app/partner/v2/goods?max=50&nodeId=".$nodeId;
        }
        else {
            $url = "https://api.uds.app/partner/v2/goods?max=50";
        }

        $client = new UdsClient($companyId,$apiKeyUds);
        $json = $client->get($url);

        if (count($json->rows) == 0) {
            return;
        }

        foreach ($json->rows as $row) {
            $currId = "".$row->id;
            if ($row->data->type == "ITEM"){
                $urlToFind = "https://online.moysklad.ru/api/remap/1.2/entity/product?filter="
                    .$hrefMsAttribProduct."=".$currId;
                $clientMs = new MsClient($apiKeyMs);
                $json = $clientMs->get($urlToFind);
                if ($json->meta->size > 0){
                    $this->updateProductInMs($row,$json->rows[0]->id,$apiKeyMs);
                }
            }
            elseif ($row->data->type == "CATEGORY"){
                $this->findNodesUds($apiKeyMs,$companyId,$apiKeyUds,$hrefMsAttribProduct,$currId);
            }
        }
    }

    private function updateProductInMs($productUds, $idProductMs,$apiKeyMs)
    {
        $url = "https://online.moysklad.ru/api/remap/1.2/entity/product/".$idProductMs;
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

            $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Не применять бонусную программу (UDS)", $apiKeyMs),
                    "name" => "Не применять бонусную программу (UDS)",
                    "value" => $productUds->data->offer->skipLoyalty,
            ];
                $countAttribute++;

            $bodyProduct["salePrices"][1] = [
                "value" => $productUds->data->offer->offerPrice * 100,
                "currency" => $this->currencyHookService->getKzCurrency($apiKeyMs),
                "priceType" => $this->priceTypeHookService->getPriceType("Акционный",$apiKeyMs),
            ];
        }
        else {
            $bodyProduct["salePrices"][1] = [
                "value" => 0,
                "currency" => $this->currencyHookService->getKzCurrency($apiKeyMs),
                "priceType" => $this->priceTypeHookService->getPriceType("Акционный",$apiKeyMs),
            ];

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Акционный товар (UDS)", $apiKeyMs),
                "name" => "Акционный товар (UDS)",
                "value" => false,
            ];
            $countAttribute++;
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Не применять бонусную программу (UDS)", $apiKeyMs),
                "name" => "Не применять бонусную программу (UDS)",
                "value" => false,
            ];
            $countAttribute++;
        }

        if ($productUds->data->increment != null){

            $measurement = $productUds->data->measurement;
            $increment = 0;

            if ($measurement == "MILLILITRE" || $measurement == "GRAM"){
                $increment = $productUds->data->increment / 1000.0;
            } elseif($measurement == "CENTIMETRE"){
                $increment = $productUds->data->increment / 100.0;
            }

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Шаг дробного значения (UDS)",$apiKeyMs),
                "name" => "Шаг дробного значения (UDS)",
                "value" => floatval($increment),
            ];
            $countAttribute++;
        }
        else {
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Шаг дробного значения (UDS)",$apiKeyMs),
                "name" => "Шаг дробного значения (UDS)",
                "value" => null,
            ];
            $countAttribute++;
        }

        if ($productUds->data->minQuantity != null){

            $measurement = $productUds->data->measurement;
            $minQuantity = 0;

            if ($measurement == "MILLILITRE" || $measurement == "GRAM"){
                $minQuantity = $productUds->data->minQuantity / 1000.0;
            } elseif($measurement == "CENTIMETRE"){
                $minQuantity = $productUds->data->minQuantity / 100.0;
            }

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Минимальный размер заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Минимальный размер заказа дробного товара (UDS)",
                "value" => floatval($minQuantity),
            ];
            $countAttribute++;
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Дробное значение товара (UDS)",$apiKeyMs),
                "name" => "Дробное значение товара (UDS)",
                "value" => true,
            ];
            $countAttribute++;

            //up min and main price

            if($productUds->data->measurement == "MILLILITRE" || $productUds->data->measurement == "GRAM"){
                $bodyProduct["salePrices"][0]["value"] *= 1000;
                if($productUds->data->offer == null){
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->price,
                    ];
                } else {
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->offer->offerPrice / 1000.0,
                    ];
                }
                $countAttribute++;
            }
            elseif ($productUds->data->measurement == "CENTIMETRE"){
                $bodyProduct["salePrices"][0]["value"] *= 100;
                if($productUds->data->offer == null){
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->price,
                    ];
                } else {
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->offer->offerPrice / 100.0,
                    ];
                }
                $countAttribute++;
            }

        }
        else {
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Минимальный размер заказа дробного товара (UDS)",$apiKeyMs),
                "name" => "Минимальный размер заказа дробного товара (UDS)",
                "value" => null,
            ];
            $countAttribute++;
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Дробное значение товара (UDS)",$apiKeyMs),
                "name" => "Дробное значение товара (UDS)",
                "value" => false,
            ];
            $countAttribute++;
        }

        if ($productUds->data->measurement == "METRE"){

            //if ($countAttribute == 0) $countAttribute++;
            if ($productUds->data->offer == null){
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->price / 100.0,
                ];
            } else {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->offer->offerPrice / 100.0,
                ];
            }

            $countAttribute++;
        }
        elseif ($productUds->data->measurement == "LITRE" || $productUds->data->measurement == "KILOGRAM"){

            // if ($countAttribute == 0) $countAttribute++;

            if($productUds->data->offer == null){
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->price / 1000.0,
                ];
            } else {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)",$apiKeyMs),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->offer->offerPrice / 1000.0,
                ];
            }


            $countAttribute++;
        }

        //if ($countAttribute == 0) $countAttribute++;

        $bodyProduct["attributes"][$countAttribute] = [
            "meta" => $this->attributeHookService
                ->getProductAttribute("id (UDS)",$apiKeyMs),
            "name" => "id (UDS)",
            "value" => "".$productUds->id,
        ];

        //dd($bodyProduct);

        $client = new MsClient($apiKeyMs);
        try {
            $client->put($url,$bodyProduct);
        } catch (ClientException $e){
            dd($e);
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

}
