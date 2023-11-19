<?php

namespace App\Services\WebhookMS;


use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\AdditionalServices\ImgService;
use GuzzleHttp\Exception\BadResponseException;

class WebHookStock
{
    private getSettingVendorController $Setting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function initiation($accountId, $reportUrl): string
    {
        $this->Setting = new getSettingVendorController($accountId);
        $this->msClient = new MsClient($this->Setting->TokenMoySklad);
        $this->udsClient = new UdsClient($this->Setting->companyId, $this->Setting->TokenUDS);

        try {
            $BODY = $this->msClient->get($reportUrl);
        } catch (BadResponseException $e) {
            return 'Ошибка: '.$e->getMessage();
        }

        if (count($BODY)>0){
            return $this->ProductsQuantity($BODY);
        } else {
            return 'Изменение невозможно, пустой массив';
        }


    }

    private function ProductsQuantity(mixed $BODY): string
    {
        $Stock = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store/'.$BODY[0]->storeId);
        if ($Stock->name == $this->Setting->Store)
        foreach ($BODY as $item){
            try {
                $product = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/product/'.$item->assortmentId);
            }catch (BadResponseException){
                continue;
            }
            if (property_exists($product, 'attributes')) {
                $ID = $this->attributes($product->attributes, 'id (UDS)');
                if ($ID != 0) {
                    try {
                        $ProductUDS = $this->udsClient->get('https://api.uds.app/partner/v2/goods/'.$ID);
                        $ProductUDS->data->inventory->inStock = $item->stock;
                        $this->udsClient->put('https://api.uds.app/partner/v2/goods/'.$ID, $ProductUDS);
                    } catch (BadResponseException) {
                        continue;
                    }
                }
            } else continue;
        }
        else return "Склад не соответствует настройкам";
        return "Все возможные количество товар изменились";
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
