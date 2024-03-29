<?php

namespace App\Services\newProductService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\AdditionalServices\ImgService;
use App\Services\MetaServices\MetaHook\AttributeHook;
use App\Services\MetaServices\MetaHook\CurrencyHook;
use App\Services\MetaServices\MetaHook\PriceTypeHook;
use App\Services\MetaServices\MetaHook\UomHook;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class createProductForMS
{

    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;
    private ImgService $imgService;
    private string $apiKeyMs;
    private AttributeHook $attributeHookService;
    private CurrencyHook $currencyHookService;
    private PriceTypeHook $priceTypeHookService;
    private UomHook $uomHookService;

    public function __construct($data)
    {
        $mainSetting = new getMainSettingBD($data['accountId']);

        $this->setting = json_decode(json_encode($data));
        $this->msClient = new MsClient($mainSetting->tokenMs);
        $this->udsClient = new UdsClient($mainSetting->companyId, $mainSetting->TokenUDS);
        $this->imgService = new ImgService();

        $this->attributeHookService = new AttributeHook($this->msClient);
        $this->currencyHookService = new CurrencyHook($this->msClient);
        $this->priceTypeHookService = new PriceTypeHook($this->msClient);
        $this->uomHookService = new UomHook($this->msClient);
    }

    public function initialization(): array
    {
        $accountId = $this->setting->accountId;
        $setting = new getSettingVendorController($accountId);
        $this->apiKeyMs = $setting->TokenMoySklad;
        $parentFolder = $this->getFolderMetaById();


        $hrefAttrib = $this->getProductAttribute();
        if ($hrefAttrib == null)  return ["message" => "Отсутствуют доп поля", ];
        $offset = 0;
        while ($this->haveRowsInResponse($url, $offset)) {
            $productsUds = $this->udsClient->get($url);
            foreach ($productsUds->rows as $productUds) {
                $currId = "" . $productUds->id;
                if ($productUds->data->type == "ITEM") {

                    if (!$this->isProductExistsMs($currId, $hrefAttrib)) {
                        $createdProduct = $this->createProductMs($productUds, $parentFolder);
                        if ($createdProduct != null && count($productUds->imageUrls) > 0) {
                            $this->imgService->setImgMS($createdProduct, $productUds->imageUrls, $this->apiKeyMs);
                        }
                    }

                }
                elseif ($productUds->data->type == "VARYING_ITEM") {
                    if (!$this->isProductExistsMs($currId, $hrefAttrib)) {
                        $this->createVariantProduct($productUds, $parentFolder);
                    }
                }
                elseif ($productUds->data->type == "CATEGORY") {
                    $category = $this->createCategoryMs($productUds->name, $productUds->id, $parentFolder);
                    $this->addProductsByCategoryUds(
                        $hrefAttrib,
                        $category->meta,
                        $category->externalCode
                    );
                }
                else continue;
            }
            $offset += 50;
        }
        return [
            "message" => "Successful export products to MS",
        ];
    }

    private function haveRowsInResponse(&$url, $offset, $nodeId = 0): bool
    {
        $url = "https://api.uds.app/partner/v2/goods?max=50&offset=" . $offset;
        if ($nodeId > 0) { $url = $url . "&nodeId=" . $nodeId; }
        $json = $this->udsClient->get($url);
        return count($json->rows) > 0;
    }

    private function isProductExistsMs($nodeId, $hrefMsAttribProduct): bool
    {
        $json = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/entity/product?filter=" . $hrefMsAttribProduct . "=" . $nodeId);
        return ($json->meta->size > 0);
    }

    private function addProductsByCategoryUds($hrefProductId, $parentCategoryMeta, $nodeId = 0): void
    {

        $offset = 0;
        while ($this->haveRowsInResponse($url, $offset, $nodeId)) {
            $json = $this->udsClient->get($url);
            foreach ($json->rows as $row) {
                $currId = "" . $row->id;
                if ($row->data->type == "CATEGORY") {
                    $category = $this->createCategoryMs($row->name, $row->id, $parentCategoryMeta);
                    if ($category != null)
                        $this->addProductsByCategoryUds(
                            $hrefProductId,
                            $category->meta,
                            $category->externalCode
                        );
                }
                elseif ($row->data->type == "ITEM") {
                    if (!$this->isProductExistsMs($currId, $hrefProductId)) {
                        $createdProduct = $this->createProductMs($row, $parentCategoryMeta);
                        if ($createdProduct != null && count($row->imageUrls) > 0) {
                            $this->imgService->setImgMS($createdProduct, $row->imageUrls, $this->apiKeyMs);
                        }
                    }
                }
                elseif ($row->data->type == "VARYING_ITEM") {
                    if (!$this->isProductExistsMs($currId, $hrefProductId)) {
                        $this->createVariantProduct($row, $parentCategoryMeta);
                    }
                }
            }
            $offset += 50;
        }
    }

    private function createCategoryMs($nameFolder, $externalCode, $parentFolder = null)
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/productfolder";

        $jsonToCheck = $this->msClient->get($url);

        $foundedCategory = null;
        foreach ($jsonToCheck->rows as $row) {
            if ($row->externalCode == $externalCode) {
                $foundedCategory = $row;
                break;
            }
        }

        if ($foundedCategory != null) {
            return $foundedCategory;
        } else {
            //dd($nameFolder,$pathName);
            $bodyCategory["name"] = $nameFolder;
            $bodyCategory["externalCode"] = "" . $externalCode;
            if ($parentFolder != null) {
                $bodyCategory["productFolder"] = [
                    "meta" => $parentFolder,
                ];
            }
            try {
                return $this->msClient->post($url, $bodyCategory);
            } catch (ClientException) {
                return null;
            }
        }
    }

    private function createProductMs($productUds, $productFolderMeta = null)
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/product";
        $bodyProduct["name"] = $productUds->name;

        $bodyProduct["salePrices"] = [
            0 => [
                "value" => $productUds->data->price * 100,
                "currency" => $this->currencyHookService->getKzCurrency(),
                "priceType" => $this->priceTypeHookService->getPriceType("Цена продажи",  $this->setting->salesPrices),
            ],
        ];

        $nameUom = $this->getUomMsByUds($productUds->data->measurement);
        $bodyProduct["uom"] = $this->uomHookService->getUom($nameUom);

        if ($productUds->data->sku != null) {
            $bodyProduct["article"] = $productUds->data->sku;
        }

        $countAttribute = 0;
        if ($productUds->data->offer != null) {
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Акционный товар (UDS)"),
                "name" => "Акционный товар (UDS)",
                "value" => true,
            ];
            $countAttribute++;
            if ($productUds->data->offer->skipLoyalty) {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Не применять бонусную программу (UDS)"),
                    "name" => "Не применять бонусную программу (UDS)",
                    "value" => true,
                ];
                $countAttribute++;
            }
            $bodyProduct["salePrices"][1] = [
                "value" => $productUds->data->offer->offerPrice * 100,
                "currency" => $this->currencyHookService->getKzCurrency(),
                "priceType" => $this->priceTypeHookService->getPriceType("Акционный", $this->setting->promotionalPrice),
            ];
        }

        if ($productUds->data->increment != null) {

            $measurement = $productUds->data->measurement;
            $increment = 0;

            if ($measurement == "MILLILITRE" || $measurement == "GRAM") {
                $increment = $productUds->data->increment / 1000.0;
            } elseif ($measurement == "CENTIMETRE") {
                $increment = $productUds->data->increment / 100.0;
            }

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Шаг дробного значения (UDS)"),
                "name" => "Шаг дробного значения (UDS)",
                "value" => floatval($increment),
            ];
            $countAttribute++;
        }

        if ($productUds->data->minQuantity != null) {

            $measurement = $productUds->data->measurement;
            $minQuantity = 0;

            if ($measurement == "MILLILITRE" || $measurement == "GRAM") {
                $minQuantity = $productUds->data->minQuantity / 1000.0;
            } elseif ($measurement == "CENTIMETRE") {
                $minQuantity = $productUds->data->minQuantity / 100.0;
            }

            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Минимальный размер заказа дробного товара (UDS)"),
                "name" => "Минимальный размер заказа дробного товара (UDS)",
                "value" => floatval($minQuantity),
            ];
            $countAttribute++;
            $bodyProduct["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService
                    ->getProductAttribute("Дробное значение товара (UDS)"),
                "name" => "Дробное значение товара (UDS)",
                "value" => true,
            ];
            $countAttribute++;

            //up min and main price

            if ($productUds->data->measurement == "MILLILITRE" || $productUds->data->measurement == "GRAM") {
                $bodyProduct["salePrices"][0]["value"] *= 1000;
                if ($productUds->data->offer == null) {
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->price,
                    ];
                } else {
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->offer->offerPrice / 1000.0,
                    ];
                }
                $countAttribute++;
            } elseif ($productUds->data->measurement == "CENTIMETRE") {
                $bodyProduct["salePrices"][0]["value"] *= 100;
                if ($productUds->data->offer == null) {
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->price,
                    ];
                } else {
                    $bodyProduct["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService
                            ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $productUds->data->offer->offerPrice / 100.0,
                    ];
                }
                $countAttribute++;
            }

        }

        if ($productUds->data->measurement == "METRE") {

            if ($productUds->data->offer == null) {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->price / 100.0,
                ];
            } else {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->offer->offerPrice / 100.0,
                ];
            }

            $countAttribute++;
        }
        elseif ($productUds->data->measurement == "LITRE" || $productUds->data->measurement == "KILOGRAM") {

            if ($productUds->data->offer == null) {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->price / 1000.0,
                ];
            } else {
                $bodyProduct["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService
                        ->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)"),
                    "name" => "Цена минимального размера заказа дробного товара (UDS)",
                    "value" => $productUds->data->offer->offerPrice / 1000.0,
                ];
            }

            $countAttribute++;
        }

        $bodyProduct["attributes"][$countAttribute] = [
            "meta" => $this->attributeHookService
                ->getProductAttribute("id (UDS)"),
            "name" => "id (UDS)",
            "value" => "" . $productUds->id,
        ];

        $bodyProduct["externalCode"] = "" . $productUds->id;

        $bodyProduct["description"] = $productUds->data->description;

        if ($productFolderMeta != null) {
            $bodyProduct["productFolder"] = [
                "meta" => $productFolderMeta,
            ];
        }

        try {
            return $this->msClient->post($url, $bodyProduct);
        } catch (ClientException) {
            return null;
        }

    }

    private function createVariantProduct($productVar, $productFolderMeta = null): void
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/product";
        foreach ($productVar->data->variants as $variant) {
            $bodyProductVar["name"] = $variant->name . "(" . $productVar->name . ")";
            if ($variant->sku != null) {
                $bodyProductVar["article"] = $variant->sku;
            }
            $bodyProductVar["salePrices"] = [
                0 => [
                    "value" => $variant->price * 100,
                    "currency" => $this->currencyHookService->getKzCurrency(),
                    "priceType" => $this->priceTypeHookService->getPriceType("Цена продажи",  $this->setting->salesPrices),
                ],
            ];
            $bodyProductVar["uom"] = $this->uomHookService->getUom("шт");
            $countAttribute = 0;
            if ($variant->offer != null) {
                $bodyProductVar["attributes"][$countAttribute] = [
                    "meta" => $this->attributeHookService->getProductAttribute("Акционный товар (UDS)"),
                    "name" => "Акционный товар (UDS)",
                    "value" => true,
                ];
                $countAttribute++;
                if ($variant->offer->skipLoyalty) {
                    $bodyProductVar["attributes"][$countAttribute] = [
                        "meta" => $this->attributeHookService->getProductAttribute("Не применять бонусную программу (UDS)"),
                        "name" => "Не применять бонусную программу (UDS)",
                        "value" => true,
                    ];
                    $countAttribute++;
                }
                $bodyProductVar["salePrices"][1] = [
                    "value" => $variant->offer->offerPrice * 100,
                    "currency" => $this->currencyHookService->getKzCurrency(),
                    "priceType" => $this->priceTypeHookService->getPriceType("Акционный",  $this->setting->promotionalPrice),
                ];
            }
            $bodyProductVar["attributes"][$countAttribute] = [
                "meta" => $this->attributeHookService->getProductAttribute("id (UDS)"),
                "name" => "id (UDS)",
                "value" => "" . $productVar->id,
            ];

            if ($productFolderMeta != null) {
                $bodyProductVar["productFolder"] = [
                    "meta" => $productFolderMeta,
                ];
            }

            try {
                $this->msClient->post($url, $bodyProductVar);
            } catch (ClientException) {
            }
        }
    }

    private function getUomMsByUds($nameUom): string
    {
        $nameUomMs = "";
        switch ($nameUom) {
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

    private function getProductAttribute()
    {
        $json = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/product/metadata/attributes');
        $foundedMeta = null;
        foreach ($json->rows as $row) {
            if ($row->name == "id (UDS)") {
                $foundedMeta = $row->meta->href;
                break;
            } else continue;
        }
        return $foundedMeta;
    }

    private function getFolderMetaById()
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/productfolder";
        $meta = null;
        try {
            $getBody = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=name=UDS')->rows;
            if ($getBody) {
                $meta = $getBody[0]->meta;
            }
        } catch (BadResponseException) {

        }

        if ($meta == null) {
            $meta = $this->msClient->post($url, ['name' => 'UDS'])->meta;
        }

        return $meta;
    }


}
