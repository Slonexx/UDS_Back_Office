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

        if ($this->Setting->ProductFolder != "1"){ return 'Отсутствует настройки отправки товара'; }

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

    private function ProductsQuantity(mixed $BODY)
    {
        foreach ($BODY as $item){
            try {
                $Stock = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store/'.$item->storeId);
                if ($Stock->name == $this->Setting->Store){
                    $product = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/product/'.$item->assortmentId);
                    if (property_exists($product, 'attributes')) {
                        $ID = $this->attributes($product->attributes, 'id (UDS)');
                        if ($ID == 0) { continue; }
                        else {
                            $ProductUDS = $this->udsClient->get('https://api.uds.app/partner/v2/goods/'.$ID);
                            $ProductUDS->data->inventory->inStock = $item->stock;
                           $this->udsClient->put('https://api.uds.app/partner/v2/goods/'.$ID, $ProductUDS);
                        }
                    } else continue;
                }
                else continue;
            } catch (BadResponseException $e) {
                try {
                    $Stock = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store/'.$item->storeId);
                    if ($Stock->name == $this->Setting->Store){
                        $product = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/variant/'.$item->assortmentId);
                        if (property_exists($product, 'attributes')) {
                            $ID = $this->attributes($product->attributes, 'id (UDS)');
                            if ($ID == 0) { continue; }
                            else {
                                $ProductUDS = $this->udsClient->get('https://api.uds.app/partner/v2/goods/'.$ID);
                                $ProductUDS->data->inventory->inStock = $item->stock;
                                $this->udsClient->put('https://api.uds.app/partner/v2/goods/'.$ID, $ProductUDS);
                            }
                        } else continue;
                    }
                    else continue;
                } catch (BadResponseException $e) {
                    continue;
                   // dd($item);
                }
            }
        }
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
