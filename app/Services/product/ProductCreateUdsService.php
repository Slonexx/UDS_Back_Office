<?php

namespace App\Services\product;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\mainURL;
use App\Models\ProductFoldersByAccountID;
use App\Services\AdditionalServices\ImgService;
use App\Services\AdditionalServices\StockProductService;
use App\Services\MetaServices\Entity\StoreService;
use App\Services\MetaServices\MetaHook\AttributeHook;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class ProductCreateUdsService
{
    private AttributeHook $attributeHookService;

    private StockProductService $stockProductService;
    private StoreService $storeService;
    private ImgService $imgService;

    //Add products to UDS from MS

    public function __construct(AttributeHook $attributeHookService, StockProductService $stockProductService, StoreService $storeService, ImgService $imgService)
    {
        $this->attributeHookService = $attributeHookService;
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

    public function getUdsCheck($companyId, $apiKeyUds, $accountId): array
    {
        set_time_limit(3600);
        $this->findNodesUds($nodeIds, $companyId, $apiKeyUds, $accountId);
        if ($nodeIds == null) {
            $nodeIds = [
                "productIds" => [],
                "categoryIds" => [],
            ];
        }
        return $nodeIds;
    }

    private function getMs($folderName, $apiKeyMs)
    {
        $client = new MsClient($apiKeyMs);

        if ($folderName == null) {
            $url = "https://api.moysklad.ru/api/remap/1.2/entity/product";
        } else {
            $url = "https://api.moysklad.ru/api/remap/1.2/entity/product?filter=pathName~" . $folderName;
        }
        $result = $client->get($url);

        if ($folderName == null) {
            $url = "https://api.moysklad.ru/api/remap/1.2/entity/service";
        } else {
            $url = "https://api.moysklad.ru/api/remap/1.2/entity/service?filter=pathName~" . $folderName;
        }
        $result->rows = array_merge($result->rows, $client->get($url)->rows);

        return $result;
    }

    private function notAddedInUds($apiKeyMs, $apiKeyUds, $companyId, $ProductFolder, $storeName, $accountId): array
    {
        set_time_limit(7200);
        $ARR_PRODUCT[] = null;
        $productsUds = $this->getUdsCheck($companyId, $apiKeyUds, $accountId);
        $storeHref = $this->storeService->getStore($storeName, $apiKeyMs)->href;
        if (!array_key_exists('categoryIds', $productsUds)) {
            $productsUds['categoryIds'] = [];
        }
        if (!array_key_exists('productIds', $productsUds)) {
            $productsUds['productIds'] = [];
        }

        //dd($productsUds);

        if ($ProductFolder == "1") {
            $find = ProductFoldersByAccountID::query()->where('accountId', $accountId);
            foreach ($find->get()->all() as $item) {
                $folderName = $item->getAttributes()['FolderName'];
                if ($folderName == "Корневая папка") $folderName = null;
                $this->addCategoriesToUds($productsUds["categoryIds"], $folderName, $apiKeyMs, $companyId, $apiKeyUds, $accountId, '');
                $productsUds = $this->getUdsCheck($companyId, $apiKeyUds, $accountId);
                if (!array_key_exists('categoryIds', $productsUds)) {
                    $productsUds['categoryIds'] = [];
                }
                if (!array_key_exists('productIds', $productsUds)) {
                    $productsUds['productIds'] = [];
                }

                $productsMs = $this->getMs($folderName, $apiKeyMs);
                foreach ($productsMs->rows as $row) {

                    if (str_starts_with($row->pathName, $folderName)) {
                        $isProductNotAdd = true;

                        if (property_exists($row, 'pathName')) {
                            if (count(explode('/', $row->pathName)) < 3) {
                            } else $isProductNotAdd = false;
                        } else $isProductNotAdd = false;

                        if (property_exists($row, "attributes") and $isProductNotAdd) {
                            foreach ($row->attributes as $attribute) {
                                if ($attribute->name == "id (UDS)") {
                                    if (in_array($attribute->value, $productsUds["productIds"])) {
                                        $isProductNotAdd = false;
                                    }
                                }
                                if ($attribute->name == "Не выгружать товар в UDS ? (UDS)") {
                                    if ($attribute->value) $isProductNotAdd = false;
                                } else continue;
                            }
                        }

                        if ($isProductNotAdd) {
                            //dd($isProductNotAdd,$row);
                            if (property_exists($row, "productFolder")) {
                                $productFolderHref = $row->productFolder->meta->href;
                                $idNodeCategory = $this->getCategoryIdByMetaHref($productFolderHref, $apiKeyMs);
                                if (!in_array($idNodeCategory, $productsUds["categoryIds"])) {
                                    $idNodeCategory = 0;
                                }
                            } else $idNodeCategory = 0;

                            try {
                                $createdProduct = $this->createProductUds($row, $apiKeyMs, $companyId, $apiKeyUds, $storeHref, $accountId, $idNodeCategory);
                                $ARR_PRODUCT[] = $createdProduct;
                            } catch (BadResponseException) {
                                continue;
                            }
                        } else continue;
                    }


                }
            }
        }
        return [
            "message" => "Successful export products to UDS",
            'Массив товаров' => $ARR_PRODUCT
        ];
    }


    private function addCategoriesToUds($check, $pathName, $apiKeyMs, $companyId, $apiKeyUds, $accountId, $nodeId): void
    {
        $categoriesMs = null;
        if (!$this->getCategoriesMs($categoriesMs, $pathName, $apiKeyMs)) return;
        if ($pathName == null) $pathName = '';

        if ($categoriesMs != null) {
            foreach ($categoriesMs as $categoryMs) {
                $nameCategory = $categoryMs->name;
                //dd($categoriesMs, $categoryMs->externalCode, $check, in_array($categoryMs->externalCode,$check));

                if (in_array($categoryMs->externalCode, $check)) {
                    $newNodeId = $categoryMs->externalCode;
                } else {
                    if ($nodeId == "") {
                        $createdCategory = $this->createCategoryUds($nameCategory, $companyId, $apiKeyUds, $accountId);
                    } else {
                        if (count(explode('/', $categoryMs->pathName)) <= 2) {
                            $folderHref = $categoryMs->productFolder->meta->href;
                            $idNodeCategory = $this->getCategoryIdByMetaHref($folderHref, $apiKeyMs);
                            $createdCategory = $this->createCategoryUds($nameCategory, $companyId, $apiKeyUds, $accountId, $idNodeCategory);
                        } else $createdCategory = null;
                    }

                    if ($createdCategory != null and property_exists($createdCategory, 'id')) {
                        $createdCategoryId = $createdCategory->id;
                        $newNodeId = "" . $createdCategoryId;
                        $check[] = "" . $createdCategoryId;
                        $this->updateCategory($createdCategoryId, $categoryMs->id, $apiKeyMs);
                    } else {
                        $newNodeId = $categoryMs->externalCode;
                    }
                }
                if ($pathName == '') $newPath = $pathName . "" . $nameCategory;
                else $newPath = $pathName . "/" . $nameCategory;

                $this->addCategoriesToUds(
                    $check,
                    $newPath,
                    $apiKeyMs,
                    $companyId,
                    $apiKeyUds,
                    $accountId,
                    $newNodeId
                );
                //UPDATE
            }
        }
    }

    private function getCategoriesMs(&$rows, $folderName, $apiKeyMs): bool
    {

        if ($folderName != null) {
            $url = "https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName=" . $folderName;
            $client = new MsClient($apiKeyMs);
            try {
                $json = $client->get($url);
                $rows = $json->rows;
                return (true);
            } catch (ClientException) {
                return (false);
            }
        } else {
            $url = "https://api.moysklad.ru/api/remap/1.2/entity/productfolder";
            $client = new MsClient($apiKeyMs);
            try {
                $json = $client->get($url)->rows;
                $newrows = null;
                foreach ($json as $item) {
                    if (!isset($item->productFolder)) $newrows[] = $item;
                }
                $rows = $newrows;
                return (true);
            } catch (ClientException) {
                return (false);
            }
        }
    }

    private function updateCategory($createdCategoryId, $idMs, $apiKeyMs): void
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/productfolder/" . $idMs;
        $client = new MsClient($apiKeyMs);
        $body = [
            "externalCode" => "" . $createdCategoryId,
        ];
        $client->put($url, $body);
    }

    private function updateProduct($createdProduct, $idMs, $apiKeyMs): void
    {
        $url = "https://api.moysklad.ru/api/remap/1.2/entity/product/" . $idMs;
        $client = new MsClient($apiKeyMs);

        try {
            $meta = $this->attributeHookService->getProductAttribute("id (UDS)", $apiKeyMs);

            if (!property_exists($createdProduct, 'id')) {
                return;
            }

            $body = [
                "attributes" => [
                    0 => [
                        "meta" => $meta,
                        "name" => "id (UDS)",
                        "value" => "" . $createdProduct->id,
                    ],
                ],
            ];
            $nameOumUds = $createdProduct->data->measurement;

            if ($nameOumUds != "PIECE") {
                if ($createdProduct->data->offer == null) {
                    $priceDefault = $createdProduct->data->price;

                    if ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE") {
                        $priceDefault /= 1000.0;
                    } elseif ($nameOumUds == "METRE") {
                        $priceDefault /= 100.0;
                    }

                    $body["attributes"][1] = [
                        "meta" => $this->attributeHookService->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)", $apiKeyMs),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $priceDefault,
                    ];
                } else {
                    $offerPrice = $createdProduct->data->offer->offerPrice;
                    if ($createdProduct->data->increment != null && $createdProduct->data->minQuantity != null) {
                        if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM") {
                            $offerPrice /= 1000.0;
                        } elseif ($nameOumUds == "CENTIMETRE") {
                            //offer price 100
                            $offerPrice /= 100.0;
                        }
                    } elseif ($createdProduct->data->increment == null && $createdProduct->data->minQuantity == null) {
                        if ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE") {
                            $offerPrice /= 1000.0;
                        } elseif ($nameOumUds == "METRE") {
                            $offerPrice /= 100.0;
                        }
                    }
                    $body["attributes"][1] = [
                        "meta" => $this->attributeHookService->getProductAttribute("Цена минимального размера заказа дробного товара (UDS)", $apiKeyMs),
                        "name" => "Цена минимального размера заказа дробного товара (UDS)",
                        "value" => $offerPrice,
                    ];
                }
            }

            $client->put($url, $body);

        } catch (BadResponseException) {

        }

    }

    private function createProductUds(mixed $product, string $apiKeyMs, string $companyId, string $apiKeyUds, string $storeHref, string $accountId, $nodeId = 0)
    {
        $url = "https://api.uds.app/partner/v2/goods";
        $mainUrl = new mainURL();
        $Client_UDS = new UdsClient($companyId, $apiKeyUds);
        $Client_MS = new MsClient($apiKeyMs);
        $error_log = "Не удалось создать товар " . $product->name . " в UDS.";
        $bd = new BDController();

        if (property_exists($product, 'variantsCount')) {
            if ($product->variantsCount > 0) {
                if (strlen($product->name) > 100) {
                    $name = mb_substr($product->name, 0, 100);
                } else {
                    $name = $product->name;
                }

                $body = [
                    "name" => $name,
                    "data" => [
                        "type" => "VARYING_ITEM",
                        "description" => "",
                        "photos" => [],
                        "variants" => [],
                    ],
                ];

                $variant = $Client_MS->get($mainUrl->url_ms() . 'variant?filter=productid=' . $product->id)->rows;
                $variants = [];
                foreach ($variant as $id => $item) {
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
                    foreach ($item->salePrices as $item_price) {
                        if ($item_price->value > 0) {
                            $variants[$id]['price'] = $item_price->value / 100;
                            break;

                        }
                    }
                    if ($variants[$id]['price'] < 0 or $variants[$id]['price'] == null) {
                        unset($variants[$id]);
                        continue;
                    }
                    if (property_exists($product, "attributes")) {
                        foreach ($product->attributes as $attribute) {
                            if ($attribute->name == "Акционный товар (UDS)" && $attribute->value) {
                                foreach ($item->salePrices as $salePrices) {
                                    if ($salePrices->priceType->name == "Акционный") {
                                        $variants[$id]['offer']['offerPrice'] = $salePrices->value / 100;
                                    }
                                }
                            }
                            if ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value) {
                                $variants[$id]['offer']['skipLoyalty'] = $attribute->value;
                            }
                            if ($attribute->name == "Товар неограничен (UDS)" && !$attribute->value) {
                                $inStock = $Client_MS->get("https://api.moysklad.ru/api/remap/1.2/report/stock/all?" . "filter=store=" . $storeHref . ";search=" . $item->name)->rows;
                                if ($inStock) {
                                    $variants[$id]['inventory']['inStock'] = $inStock[0]->quantity;
                                }
                            }
                        }
                    }
                }

                if ($variants != []) {
                    $body['data']['variants'] = $variants;
                } else {
                    return null;
                }

            } else {
                $prices = [];

                foreach ($product->salePrices as $price) {
                    if ($price->priceType->name == "Цена продажи") {
                        $prices["salePrice"] = ($price->value / 100);
                    } else {
                        if ($price->priceType->name == "Акционный") $prices["offerPrice"] = ($price->value / 100);
                    }
                }

                if ($prices == []) {
                    $prices["salePrice"] = 0;
                    for ($index = 0; $index < count($product->salePrices); $index++) {
                        if ($prices["salePrice"] > 0) break;
                        else $prices["salePrice"] = $product->salePrices[$index]->value / 100;
                    }
                }

                if ($prices["salePrice"] <= 0) {
                    $bd->errorProductLog($accountId, $error_log . " Не была указана цена товара в MS");
                    return null;
                }


                //ДО делать get UoM
                if (property_exists($product, 'uom')) $nameOumUds = $this->getUomUdsByMs($product->uom->meta->href, $apiKeyMs);
                else $nameOumUds = "";

                if ($nameOumUds == "") {
                    $bd->errorProductLog($accountId, $error_log . " Была указана некорректная ед.изм товара в MS");
                    return null;
                }

                if (strlen($product->name) > 100) {
                    $name = mb_substr($product->name, 0, 100);
                } else {
                    $name = $product->name;
                }

                if (property_exists($product, 'description')) {
                    $description = $product->description;
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

                if (property_exists($product, "attributes")) {

                    $isFractionProduct = false;
                    $isOfferProduct = false;

                    foreach ($product->attributes as $attribute) {
                        if ($attribute->name == "Дробное значение товара (UDS)" && $attribute->value == 1) {
                            $isFractionProduct = true;
                            //break;
                        } elseif ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1) {
                            $isOfferProduct = true;
                        }
                    }

                    if ($isFractionProduct && ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE" || $nameOumUds == "METRE")) {
                        $bd->errorProductLog($accountId, $error_log . " Выбранная ед.изм товара в MS, не может быть дробным товаром в UDS.");
                        return null;
                    }


                    if (isset($prices['offerPrice'])) {
                        if ($isOfferProduct && ($prices['offerPrice'] <= 0 || $prices['offerPrice'] > $prices['salePrice'])) {
                            $bd->errorProductLog($accountId, $error_log . " Акционная цена не может быть равна 0, также не может быть больше Цены продажи");
                            return null;
                        }
                    }


                    foreach ($product->attributes as $attribute) {
                        if ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1) {
                            if (isset($prices['offerPrice'])) {
                                $body["data"]["offer"]["offerPrice"] = $prices["offerPrice"];
                            }
                        } elseif ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value == 1) {
                            $body["data"]["offer"]["skipLoyalty"] = true;
                        } elseif ($attribute->name == "Шаг дробного значения (UDS)" && $isFractionProduct) {
                            $body["data"]["increment"] = (float)($attribute->value);
                            if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM") {
                                $body["data"]["increment"] *= 1000.0;
                                if ($body["data"]["increment"] >= 10000000) {
                                    $bd->errorProductLog($accountId, $error_log . " Шаг дробного значения (UDS) введен некорректно");
                                    return null;
                                }
                            } elseif ($nameOumUds == "CENTIMETRE") {
                                $body["data"]["increment"] *= 100.0;
                                if ($body["data"]["increment"] >= 1000000) {
                                    $bd->errorProductLog($accountId, $error_log . " Шаг дробного значения (UDS) введен некорректно");
                                    return null;
                                }
                            }
                        } elseif ($attribute->name == "Минимальный размер заказа дробного товара (UDS)" && $isFractionProduct) {
                            $body["data"]["minQuantity"] = (float)($attribute->value);
                            if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM") {
                                $body["data"]["price"] /= 1000;
                                $body["data"]["minQuantity"] *= 1000.0;
                                if ($body["data"]["minQuantity"] >= 10000000) {
                                    $bd->errorProductLog($accountId, $error_log . " Минимальный размер заказа дробного товара (UDS) введен некорректно");
                                    return null;
                                }
                            } elseif ($nameOumUds == "CENTIMETRE") {
                                $body["data"]["price"] /= 100;
                                $body["data"]["minQuantity"] *= 100.0;
                                if ($body["data"]["minQuantity"] >= 1000000) {
                                    $bd->errorProductLog($accountId, $error_log . " Минимальный размер заказа дробного товара (UDS) введен некорректно");
                                    return null;
                                }
                            }
                        } elseif ($attribute->name == "Товар неограничен (UDS)") {
                            if ($attribute->value == 1) {
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

                    if (!array_key_exists("inventory", $body["data"])) {
                        $body["data"]["inventory"]["inStock"] = $this->stockProductService->getProductStockMs($product->externalCode, $storeHref, $apiKeyMs);
                    }

                    if ($isFractionProduct && (
                            !array_key_exists("increment", $body["data"]) || !array_key_exists("minQuantity", $body["data"]))) {
                        //dd(($body));
                        $bd = new BDController();
                        $bd->errorProductLog($accountId, $error_log . " У дробного товара не введено Минимальный размер заказа или Шаг дробного значения");
                        return null;
                    }
                    if ($isFractionProduct) {
                        if ($body["data"]["minQuantity"] < $body["data"]["increment"]) {
                            $bd = new BDController();
                            $bd->errorProductLog($accountId, $error_log . " У дробного товара Шаг дробного значения, не может быть больше Минимального размера заказа");
                            return null;
                        }
                    }

                    if ($isFractionProduct) {
                        $dPrice = explode('.', "" . $body["data"]["price"]);
                        if (count($dPrice) > 1 && strlen($dPrice[1]) > 2) {
                            $bd->errorProductLog($accountId, $error_log . " У товара цена имеет 3 числа после запятой (дробная часть)");
                            return null;
                        }
                    }

                    if ($nameOumUds == "PIECE") {
                        $body["data"]["minQuantity"] = null;
                        $body["data"]["increment"] = null;
                    }

                }

                if (property_exists($product, "article")) {
                    $body["data"]["sku"] = $product->article;
                }

            }

        } else {
            $prices = [];

            foreach ($product->salePrices as $price) {
                if ($price->priceType->name == "Цена продажи") {
                    $prices["salePrice"] = ($price->value / 100);
                } else {
                    if ($price->priceType->name == "Акционный") $prices["offerPrice"] = ($price->value / 100);
                }
            }

            if ($prices == []) {
                $prices["salePrice"] = 0;
                for ($index = 0; $index < count($product->salePrices); $index++) {
                    if ($prices["salePrice"] > 0) break;
                    else $prices["salePrice"] = $product->salePrices[$index]->value / 100;
                }
            }

            if ($prices["salePrice"] <= 0) {
                $bd->errorProductLog($accountId, $error_log . " Не была указана цена товара в MS");
                return null;
            }


            //ДО делать get UoM
            if (property_exists($product, 'uom')) $nameOumUds = $this->getUomUdsByMs($product->uom->meta->href, $apiKeyMs);
            else $nameOumUds = "";

            if ($nameOumUds == "") {
                $bd->errorProductLog($accountId, $error_log . " Была указана некорректная ед.изм товара в MS");
                return null;
            }

            if (strlen($product->name) > 100) {
                $name = mb_substr($product->name, 0, 100);
            } else {
                $name = $product->name;
            }

            if (property_exists($product, 'description')) {
                $description = $product->description;
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

            if (property_exists($product, "attributes")) {

                $isFractionProduct = false;
                $isOfferProduct = false;

                foreach ($product->attributes as $attribute) {
                    if ($attribute->name == "Дробное значение товара (UDS)" && $attribute->value == 1) {
                        $isFractionProduct = true;
                        //break;
                    } elseif ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1) {
                        $isOfferProduct = true;
                    }
                }

                if ($isFractionProduct && ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE" || $nameOumUds == "METRE")) {
                    $bd->errorProductLog($accountId, $error_log . " Выбранная ед.изм товара в MS, не может быть дробным товаром в UDS.");
                    return null;
                }


                if (isset($prices['offerPrice'])) {
                    if ($isOfferProduct && ($prices['offerPrice'] <= 0 || $prices['offerPrice'] > $prices['salePrice'])) {
                        $bd->errorProductLog($accountId, $error_log . " Акционная цена не может быть равна 0, также не может быть больше Цены продажи");
                        return null;
                    }
                }


                foreach ($product->attributes as $attribute) {
                    if ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1) {
                        if (isset($prices['offerPrice'])) {
                            $body["data"]["offer"]["offerPrice"] = $prices["offerPrice"];
                        }
                    } elseif ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value == 1) {
                        $body["data"]["offer"]["skipLoyalty"] = true;
                    } elseif ($attribute->name == "Шаг дробного значения (UDS)" && $isFractionProduct) {
                        $body["data"]["increment"] = (float)($attribute->value);
                        if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM") {
                            $body["data"]["increment"] *= 1000.0;
                            if ($body["data"]["increment"] >= 10000000) {
                                $bd->errorProductLog($accountId, $error_log . " Шаг дробного значения (UDS) введен некорректно");
                                return null;
                            }
                        } elseif ($nameOumUds == "CENTIMETRE") {
                            $body["data"]["increment"] *= 100.0;
                            if ($body["data"]["increment"] >= 1000000) {
                                $bd->errorProductLog($accountId, $error_log . " Шаг дробного значения (UDS) введен некорректно");
                                return null;
                            }
                        }
                    } elseif ($attribute->name == "Минимальный размер заказа дробного товара (UDS)" && $isFractionProduct) {
                        $body["data"]["minQuantity"] = (float)($attribute->value);
                        if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM") {
                            $body["data"]["price"] /= 1000;
                            $body["data"]["minQuantity"] *= 1000.0;
                            if ($body["data"]["minQuantity"] >= 10000000) {
                                $bd->errorProductLog($accountId, $error_log . " Минимальный размер заказа дробного товара (UDS) введен некорректно");
                                return null;
                            }
                        } elseif ($nameOumUds == "CENTIMETRE") {
                            $body["data"]["price"] /= 100;
                            $body["data"]["minQuantity"] *= 100.0;
                            if ($body["data"]["minQuantity"] >= 1000000) {
                                $bd->errorProductLog($accountId, $error_log . " Минимальный размер заказа дробного товара (UDS) введен некорректно");
                                return null;
                            }
                        }
                    } elseif ($attribute->name == "Товар неограничен (UDS)") {
                        if ($attribute->value == 1) {
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

                if (!array_key_exists("inventory", $body["data"])) {
                    $body["data"]["inventory"]["inStock"] = $this->stockProductService->getProductStockMs($product->externalCode, $storeHref, $apiKeyMs);
                }

                if ($isFractionProduct && (
                        !array_key_exists("increment", $body["data"]) || !array_key_exists("minQuantity", $body["data"]))) {

                    $bd = new BDController();
                    $bd->errorProductLog($accountId, $error_log . " У дробного товара не введено Минимальный размер заказа или Шаг дробного значения");
                    return null;
                }
                if ($isFractionProduct) {
                    if ($body["data"]["minQuantity"] < $body["data"]["increment"]) {
                        $bd = new BDController();
                        $bd->errorProductLog($accountId, $error_log . " У дробного товара Шаг дробного значения, не может быть больше Минимального размера заказа");
                        return null;
                    }
                }

                if ($isFractionProduct) {
                    $dPrice = explode('.', "" . $body["data"]["price"]);
                    if (count($dPrice) > 1 && strlen($dPrice[1]) > 2) {
                        $bd->errorProductLog($accountId, $error_log . " У товара цена имеет 3 числа после запятой (дробная часть)");
                        return null;
                    }
                }

                if ($nameOumUds == "PIECE") {
                    $body["data"]["minQuantity"] = null;
                    $body["data"]["increment"] = null;
                }

            }

            if (property_exists($product, "article")) {
                $body["data"]["sku"] = $product->article;
            }

        }
        if ($nodeId > 0) {
            $body["nodeId"] = intval($nodeId);
        }
        if (property_exists($product, 'images')) {
            $imgIds = $this->imgService->setImgUDS($product->images->meta->href, $apiKeyMs, $companyId, $apiKeyUds);
            $body["data"]["photos"] = $imgIds;
        }

        try {
            $createdProduct = $Client_UDS->post($url,$body);

            if ($createdProduct != null) { $this->updateProduct($createdProduct, $product->id, $apiKeyMs); }
            return $createdProduct;
        } catch (ClientException $e) {
            $bd->errorProductLog($accountId, $e->getMessage());
            return null;
        }

    }

    private function createCategoryUds($nameCategory, $companyId, $apiKeyUds, $accountId, $nodeId = "")
    {
        $url = "https://api.uds.app/partner/v2/goods";
        $client = new UdsClient($companyId, $apiKeyUds);
        $body = [
            "name" => $nameCategory,
            "data" => [
                "type" => "CATEGORY",
            ],
        ];
        if (intval($nodeId) > 0 || $nodeId != "") {
            $body["nodeId"] = intval($nodeId);

        }
        try {
            return $client->post($url, $body);
        } catch (ClientException $e) {

            $bd = new BDController();
            $bd->errorProductLog($accountId, $e->getMessage());
            return null;
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

    private function getCategoryIdByMetaHref($href, $apiKeyMs)
    {
        $client = new MsClient($apiKeyMs);
        return $client->get($href)->externalCode;
    }

    private function getUomUdsByMs($href, $apiKeyMs): string
    {
        $client = new MsClient($apiKeyMs);
        $json = $client->get($href);

        if (property_exists($json, 'description')) {
            return match ($json->description) {
                "Штука" => "PIECE",
                "Сантиметр" => "CENTIMETRE",
                "Метр" => "METRE",
                "Миллиметр" => "MILLILITRE",
                "Литр; кубический дециметр" => "LITRE",
                "Грамм" => "GRAM",
                "Килограмм" => "KILOGRAM",
                default => "",
            };
        } else return "";
    }



}
