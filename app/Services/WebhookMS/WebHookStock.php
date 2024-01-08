<?php

namespace App\Services\WebhookMS;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Models\newProductModel;
use GuzzleHttp\Exception\BadResponseException;

class WebHookStock
{
    private getSettingVendorController $setting;
    private MsClient $msClient;
    private UdsClient $udsClient;

    public function initiation($accountId, $reportUrl): string
    {
        $this->setting = new getSettingVendorController($accountId);
        $this->msClient = new MsClient($this->setting->TokenMoySklad);
        $this->udsClient = new UdsClient($this->setting->companyId, $this->setting->TokenUDS);

        try {
            $body = $this->msClient->get($reportUrl);
        } catch (BadResponseException $e) {
            return 'Ошибка: ' . $e->getMessage();
        }

        if (count($body) > 0) {
            return $this->updateProductsQuantity($body);
        } else {
            return 'Изменение невозможно, пустой массив';
        }
    }

    private function updateProductsQuantity(array $body): string
    {
        $settingStore = newProductModel::where('accountId', $this->setting->accountId)->get()->first();
        if ($settingStore === null) {
            return "Отсутствует настройки сохранения";
        }

        $stock = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/store/' . $body[0]->storeId);
        if ($stock->name !== $settingStore->Store) {
            return "Склад не соответствует настройкам";
        }

        foreach ($body as $item) {
            try {
                $product = $this->msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/product/' . $item->assortmentId);
            } catch (BadResponseException) {
                continue;
            }

            if (property_exists($product, 'attributes')) {
                $id = $this->getAttributesValue($product->attributes, 'id (UDS)');
                if ($id !== 0) {
                    try {
                        $productUDS = $this->udsClient->get('https://api.uds.app/partner/v2/goods/' . $id);
                        $productUDS->data->inventory->inStock = $item->stock;
                        $this->udsClient->put('https://api.uds.app/partner/v2/goods/' . $id, $productUDS);
                    } catch (BadResponseException) {
                        continue;
                    }
                }
            }
        }

        return "Все возможные количество товар изменились";
    }

    private function getAttributesValue(array $attributes, string $name): int
    {
        $value = 0;
        foreach ($attributes as $item) {
            if ($name === $item->name) {
                $value = $item->value;
            }
        }
        return $value;
    }
}
