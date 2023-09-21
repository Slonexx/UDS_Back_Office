<?php

namespace App\Services\newProductService;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Services\AdditionalServices\ImgService;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;

class applicationCreatingProductForUDS
{

    private mixed $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;
    private ImgService $imgService;

    public function __construct($data, $ms, $uds)
    {
        $this->setting = $data;
        $this->msClient = $ms;
        $this->udsClient = $uds;
        $this->imgService = new ImgService();
    }


    public function createProductUds(mixed $product)
    {
        $url = "https://api.uds.app/partner/v2/goods";

        if (property_exists($product, 'variantsCount')) {
            if ($product->variantsCount > 0) {
                $body = $this->prepareVaryingItemBody($product);
            } else {
                $body = $this->prepareRegularItemBody($product);
            }
        } else {
            $body = $this->prepareRegularItemBody($product);
        }

        if ($body === null) {
            return null;
        }

        $this->processProductDetails($product, $body);

        try {
            $createdProduct = $this->udsClient->post($url, $body);

            if ($createdProduct != null) {
                $this->updateProduct($createdProduct, $product);
            }

            return $createdProduct;
        } catch (ClientException) {
            return null;
        }
    }

    private function prepareVaryingItemBody($product): ?array
    {
        $name = $this->getShortenedName($product->name);
        $variants = $this->prepareVariants($product);

        if (empty($variants)) {
            return null;
        }

        return [
            "name" => $name,
            "data" => [
                "type" => "VARYING_ITEM",
                "description" => "",
                "photos" => [],
                "variants" => $variants,
            ],
        ];
    }

    private function prepareRegularItemBody($product): array
    {
        $name = $this->getShortenedName($product->name);
        $prices = $this->getPrices($product);
        $nameOumUds = null;
        $description = "";

        if (property_exists($product, 'uom')) {
            $nameOumUds = $this->getUomUdsByMs($product->uom->meta->href);
        }

        if (property_exists($product, 'description')) {
            $description = $product->description;
        }

        $body = [
            "name" => $name,
            "data" => [
                "type" => "ITEM",
                "price" => $prices["salePrice"],
                "measurement" => $nameOumUds,
                "inventory" => ['inStock' => 0],
                "description" => $description,
            ],
        ];

        $inStock = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/report/stock/all?filter=store=https://api.moysklad.ru/api/remap/1.2/entity/store/" . $this->setting->Store . ";search=" . $product->name)->rows;
        if ($inStock) {
            $body['data']['inventory']['inStock'] = $inStock[0]->quantity;
        }
        if (property_exists($product, "attributes")) {
            $this->handleAttributes($product, $body, $nameOumUds);
        }


        if (property_exists($product, "article")) {
            $body["data"]["sku"] = $product->article;
        }

        return $body;
    }

    private function getShortenedName($name)
    {
        return strlen($name) > 100 ? mb_substr($name, 0, 100) : $name;
    }

    private function prepareVariants($product): array
    {
        $variants = [];
        foreach ($this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/variant?filter=productid=' . $product->id)->rows as $id => $item) {
            $variants[$id] = [
                'name' => $item->name,
                'sku' => null,
                'price' => null,
                'offer' => [
                    'offerPrice' => null,
                    'skipLoyalty' => false,
                ],
                'inventory' => [
                    'inStock' => 0,
                ],
            ];

            $inStock = $this->msClient->get("https://api.moysklad.ru/api/remap/1.2/report/stock/all?filter=store=https://api.moysklad.ru/api/remap/1.2/entity/store/" . $this->setting->Store . ";search=" . $item->name)->rows;
            if ($inStock) {
                $variants[$id]['inventory']['inStock'] = $inStock[0]->quantity;
            }


            foreach ($item->salePrices as $item_price) {
                if ($item_price->priceType->id == $this->setting->salesPrices) {
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
                            if ($salePrices->priceType->id == $this->setting->promotionalPrice) {
                                if ($variants[$id]['price'] != $salePrices->value / 100) {
                                    $variants[$id]['offer']['offerPrice'] = $salePrices->value / 100;
                                }
                            }
                        }
                    }

                    if ($attribute->name == "Не применять бонусную программу (UDS)" && $attribute->value) {
                        $variants[$id]['offer']['skipLoyalty'] = $attribute->value;
                    }

                    if ($attribute->name == "Товар неограничен (UDS)" && !$attribute->value) {
                        $variants[$id]['inventory']['inStock'] = null;
                    }
                }
            }
        }

        return $variants;
    }

    private function getPrices($product): array
    {
        $prices = [];

        // Loop through sale prices and prepare data...
        foreach ($product->salePrices as $price) {
            if ($price->priceType->id == $this->setting->salesPrices) {
                $prices["salePrice"] = ($price->value / 100);
            }
            elseif ($this->setting->salesPrices != $this->setting->promotionalPrice and $price->priceType->id == $this->setting->promotionalPrice)
                $prices["offerPrice"] = ($price->value / 100);
        }

        return $prices;
    }

    private function handleAttributes($product, &$body, $nameOumUds): void
    {

        $isFractionProduct = false;

        foreach ($product->attributes as $attribute) {
            if ($attribute->name == "Дробное значение товара (UDS)" && $attribute->value == 1) {
                $isFractionProduct = true;
                //break;
            } elseif ($attribute->name == "Акционный товар (UDS)" && $attribute->value == 1) {
                $isOfferProduct = true;
            }
        }


        if ($isFractionProduct && ($nameOumUds == "KILOGRAM" || $nameOumUds == "LITRE" || $nameOumUds == "METRE")) {
            return;
        }

        if (isset($prices['offerPrice'])) {
            if ($isOfferProduct && ($prices['offerPrice'] <= 0 || $prices['offerPrice'] > $prices['salePrice'])) {
                return;
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
                        return;
                    }
                } elseif ($nameOumUds == "CENTIMETRE") {
                    $body["data"]["increment"] *= 100.0;
                    if ($body["data"]["increment"] >= 1000000) {
                        return;
                    }
                }
            } elseif ($attribute->name == "Минимальный размер заказа дробного товара (UDS)" && $isFractionProduct) {
                $body["data"]["minQuantity"] = (float)($attribute->value);
                if ($nameOumUds == "MILLILITRE" || $nameOumUds == "GRAM") {
                    $body["data"]["price"] /= 1000;
                    $body["data"]["minQuantity"] *= 1000.0;
                    if ($body["data"]["minQuantity"] >= 10000000) {
                        return;
                    }
                } elseif ($nameOumUds == "CENTIMETRE") {
                    $body["data"]["price"] /= 100;
                    $body["data"]["minQuantity"] *= 100.0;
                    if ($body["data"]["minQuantity"] >= 1000000) {
                        return;
                    }
                }
            } elseif ($attribute->name == "Товар неограничен (UDS)") {
                if ($attribute->value) {
                    $body["data"]["inventory"]["inStock"] = null;
                }
            }
        }

        if ($isFractionProduct && (!array_key_exists("increment", $body["data"]) || !array_key_exists("minQuantity", $body["data"]))) {
            return;
        }
        if ($isFractionProduct) {
            if ($body["data"]["minQuantity"] < $body["data"]["increment"]) {
                return;
            }
        }
        if ($isFractionProduct) {
            $dPrice = explode('.', "" . $body["data"]["price"]);
            if (count($dPrice) > 1 && strlen($dPrice[1]) > 2) {
                return;
            }
        }
        if ($nameOumUds == "PIECE") {
            $body["data"]["minQuantity"] = null;
            $body["data"]["increment"] = null;
        }
    }

    private function processProductDetails($product, &$body): void
    {
        if (property_exists($product, 'productFolder')) {
            $productFolder = $this->msClient->get($product->productFolder->meta->href);
            if (preg_match('/^[0-9]+$/', $productFolder->externalCode)) {
                $body["nodeId"] = intval($productFolder->externalCode);
            }
        }

        if (property_exists($product, 'images')) {
            $imgIds = $this->imgService->setImgUDS($product->images->meta->href, $this->setting->accountId);
            $body["data"]["photos"] = $imgIds;
        }
    }



    private function updateProduct($newProductUDS, $productMS): void
    {
        try {
            $attributeMeta = $this->getProductAttribute($productMS->meta->metadataHref . '/attributes', "id (UDS)");

            if (!property_exists($newProductUDS, 'id')) {
                return;
            }

            $updatedAttribute = [
                "meta" => $attributeMeta,
                "name" => "id (UDS)",
                "value" => (string) $newProductUDS->id,
            ];

            $this->msClient->put($productMS->meta->href, [
                "attributes" => [$updatedAttribute],
            ]);

        } catch (BadResponseException) {
            // Обработка конкретного исключения или логирование ошибки
        }
    }


    private function getUomUdsByMs($href): string
    {
        $json = $this->msClient->get($href);

        if (property_exists($json, 'description')) {
            return match ($json->description) {
                "Сантиметр" => "CENTIMETRE",
                "Метр" => "METRE",
                "Миллиметр" => "MILLILITRE",
                "Литр; кубический дециметр" => "LITRE",
                "Грамм" => "GRAM",
                "Килограмм" => "KILOGRAM",
                default => "PIECE",
            };
        } else return "PIECE";
    }
    public function getProductAttribute($metadataHref, $nameAttribute)
    {
        $json = $this->msClient->get($metadataHref);
        $foundedMeta = null;
        foreach ($json->rows as $row) {
            if ($row->name == $nameAttribute) {
                $foundedMeta = $row->meta;
                break;
            } else continue;
        }
        return $foundedMeta;
    }

}
