<?php

namespace App\Services\WebhookMS;


use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Services\AdditionalServices\ImgService;
use GuzzleHttp\Exception\BadResponseException;

class WebHookProductFolder
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

        try {
            $BodyProductFolderForMS = $this->msClient->get($events['0']['meta']['href']);
        } catch (BadResponseException $e) {
            return 'Ошибка: '.$e->getMessage();
        }

        return $this->createBodyForUDS($BodyProductFolderForMS);
    }

    private function createBodyForUDS(mixed $BodyProductFolderForMS): string
    {
        try {
            if ((int) $BodyProductFolderForMS->externalCode > 1000)
                $CATEGORY = $this->udsClient->get('https://api.uds.app/partner/v2/goods/'.$BodyProductFolderForMS->externalCode);
            else return 'Отсутствует внешний идентификатор категория в UDS';
        } catch (BadResponseException $e) {
            return 'Отсутствует внешний идентификатор категория в UDS и ошибка: '.$e->getMessage();
        }

        $Body = [
            "name" => $BodyProductFolderForMS->name,
            "nodeId" => null,
            "data" => [
                "type" => "CATEGORY",
            ],
        ];

        if (property_exists($BodyProductFolderForMS,"productFolder")){
            $productFolder = $this->msClient->get($BodyProductFolderForMS->productFolder->meta->href);
            try {
                if ((int) $productFolder->externalCode > 1000)
                    $CATEGORY = $this->udsClient->get('https://api.uds.app/partner/v2/goods/'.$BodyProductFolderForMS->externalCode);
                $Body['nodeId'] = $productFolder->externalCode;
            } catch (BadResponseException $e) {
                return 'Отсутствует внешний идентификатор в основной категории nodeId категория в UDS и ошибка: '.$e->getMessage();
            }
        }

        try {
            $this->udsClient->put('https://api.uds.app/partner/v2/goods/'.$BodyProductFolderForMS->externalCode, $Body);
            return 'Категория успешно изменился';
        } catch (BadResponseException $e){
            return 'Ошибка изменения товара, ошибка : '.$e->getMessage();
        }
    }

}
