<?php

namespace App\Services\WebhookMS;


use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\AdditionalServices\ImgService;
use GuzzleHttp\Exception\BadResponseException;

class WebHookProduct
{
    private getSettingVendorController $Setting;
    private ImgService $imgService;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function initiation($events): string
    {
        $accountId = $events['0']['accountId'];
        $this->Setting = new getSettingVendorController($accountId);
        $this->msClient = new MsClient($this->Setting->TokenMoySklad);
        $this->udsClient = new UdsClient($this->Setting->companyId, $this->Setting->TokenUDS);
        $this->imgService = app(ImgService::class);

        if ($this->Setting->ProductFolder != "1"){
            return 'Отсутствует настройки отправки товара';
        }
        if (!isset($events[0]['updatedFields'])){
            return 'Отсутствует изменения товара';
        }
        $href = $events['0']['meta']['href'];
        $updatedFields = $events['0']['updatedFields'];

        return $this->createBodyForUDS($href, $updatedFields);
    }

    private function createBodyForUDS(mixed $href, mixed $updatedFields): string
    {
        $BodyProduct = $this->msClient->get($href);
        //dd($BodyProduct);
        $attributesID = $this->attributes($BodyProduct->attributes, 'id (UDS)');
        if ($attributesID == 0){ return 'Отсутствует уникальный идентификатор товара (ID UDS)'; }

        try { $BodyUDS = $this->udsClient->get('https://api.uds.app/partner/v2/goods/'.$attributesID);
        } catch (BadResponseException $e){
            return 'Отсутствует данный товар в UDS. Тело ответа и ошибка: '.$e->getMessage();
        }
        $type = $BodyUDS->data->type;
        if ($BodyProduct->variantsCount > 0){
            if (strlen($BodyProduct->name) > 100){ $name = mb_substr($BodyProduct->name,0,100); } else { $name = $BodyProduct->name; }

            $Body = [
                "name" => $name,
                "data" => [
                    "type" => "VARYING_ITEM",
                    "description" => "",
                    "photos" => [],
                    "variants" => [],
                ],
            ];

            if (property_exists($BodyProduct, 'description')){
                $Body["data"]['description'] = $BodyProduct->description;
            }

            $variant = $this->msClient->get('https://online.moysklad.ru/api/remap/1.2/entity/variant?filter=productid='.$BodyProduct->id)->rows;
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
                if (property_exists($BodyProduct,"attributes")){
                    foreach ($BodyProduct->attributes as $attribute) {
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
                            $variants[$id]['inventory']['inStock'] = $BodyUDS->data->variants[$id]->inventory->inStock;
                        }
                    }
                }
            }

            if ($variants){ $Body['data']['variants'] = $variants;
            }else {
                return "Ошибка товара с модификациями";
            }

        }
        else {
            $Body = [
                "name" => $BodyProduct->name,
                "nodeId" => null,
                "data" => [
                    "type" => $type,
                    "description" => "",
                    "sku" => null,
                    "price" => null,
                    "offer" => null,
                    "inventory" => ['inStock' => null],
                    "photos" => null,
                    "measurement" => "PIECE",
                ],
            ];

            if (property_exists($BodyProduct, 'description')){
                $Body['data']['description'] = $BodyProduct->description;
            }
            if (property_exists($BodyProduct, 'article')){
                $Body['data']['sku'] = $BodyProduct->article;
            }
            if (in_array('salePrices', $updatedFields)){
                $prices = [];
                foreach ($BodyProduct->salePrices as $price){
                    if ($price->priceType->name == "Цена продажи"){ $prices["salePrice"] = ($price->value / 100);
                    } else {
                        if ($price->priceType->name == "Акционный") $prices["offerPrice"] = ($price->value / 100);
                    }
                }
                if ($prices == []){
                    $prices["salePrice"] = 0;
                    for ($index = 0; $index < count($BodyProduct->salePrices); $index++){
                        if ($prices["salePrice"] > 0) break;
                        else $prices["salePrice"] = $BodyProduct->salePrices[$index]->value / 100;
                    }
                }
                if ( $prices["salePrice"] == 0 ){  return 'Цена товара должна быть больше > 0 '; }

                $Body['data']['price'] = $prices["salePrice"];
                if (isset( $prices["offerPrice"] )){
                    $Body['data']['offer'] = [
                        'offerPrice' => $prices["offerPrice"],
                        'skipLoyalty' => false,
                    ];
                }
            } else {
                $Body['data']['offer'] = $BodyUDS->data->price;
            }
            if (property_exists($BodyUDS->data, 'inventory'))
            if ($BodyUDS->data->inventory->inStock != null) {
                $Body['data']['inventory']['inStock'] = $BodyUDS->data->inventory->inStock;
            }
            $offer_skipLoyalty = $this->attributes($BodyProduct->attributes, 'Акционный товар (UDS)');
            if ($offer_skipLoyalty == true) {
                if ($Body['data']['offer'] == null) {
                    if ($BodyUDS->data->offer != null) {
                        $Body['data']['offer'] = $BodyUDS->data->offer;
                    } else {
                        $PRICE = 0;
                        foreach ($BodyProduct->salePrices as $price){
                            if ($price->priceType->name == "Акционный") $PRICE = ($price->value / 100);
                        }
                        if ($PRICE == 0) {
                            $Body['data']['offer'] = null;
                        } else {
                            $skipLoyalty = $this->attributes($BodyProduct->attributes, 'Не применять бонусную программу (UDS)');
                            if ($skipLoyalty == 0) { $skipLoyalty = false; }
                            $Body['data']['offer'] = [
                                'offerPrice' => $PRICE,
                                'skipLoyalty' => $skipLoyalty,
                            ];
                        }
                    }
                } else {
                    $skipLoyalty = $this->attributes($BodyProduct->attributes, 'Не применять бонусную программу (UDS)');
                    $PRICE = $Body['data']['offer']['offerPrice'];
                    if ($skipLoyalty == 0) { $skipLoyalty = false; }
                    if ($PRICE == 0) { $PRICE = null; $skipLoyalty = false; }
                    $Body['data']['offer'] = [
                        'offerPrice' => $PRICE,
                        'skipLoyalty' => $skipLoyalty,
                    ];
                }
            } else {
                $Body['data']['offer'] = null;
            }
        }

        if (property_exists($BodyProduct, 'productFolder')){
            $productFolder = $this->msClient->get($BodyProduct->productFolder->meta->href);
            if ((int) $productFolder->externalCode > 1000) {
                try {
                    $this->udsClient->get("https://api.uds.app/partner/v2/goods/".$productFolder->externalCode);
                    $Body['nodeId'] = $productFolder->externalCode;
                } catch (BadResponseException $e){
                    unset($Body['nodeId']);
                }
            }
        } else unset($Body['nodeId']);

        if (in_array('image', $updatedFields)){
           if (property_exists($BodyProduct,'images')){
               $imgIds = $this->imgService->setImgUDS($BodyProduct->images->meta->href,
                   $this->Setting->TokenMoySklad, $this->Setting->companyId, $this->Setting->TokenUDS );
               $Body["data"]["photos"] = $imgIds;
           } else unset( $Body["data"]["photos"]);
       } else {
           $Body['data']['photos'] = $BodyUDS->data->photos;
       }


        try {
        $this->udsClient->put('https://api.uds.app/partner/v2/goods/'.$attributesID, $Body);
        return 'Товар успешно изменился';
        } catch (BadResponseException $e){
            return 'Ошибка изменения товара, ошибка : '.$e->getMessage();
        }

    }

    private function attributes(mixed $attributes, string $Name)
    {
        $value = 0;
        foreach ($attributes as $item){
            if ($Name == $item->name){
                $value = $item->value;
            }
        }
        return $value;
    }
}
