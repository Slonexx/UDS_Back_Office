<?php

namespace App\Http\Controllers\Web\POST;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\BD\create;
use App\Http\Controllers\BD\update;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Models\orderSettingModel;
use App\Models\ProductFoldersByAccountID;
use App\Models\SettingMain;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class postController extends Controller
{
    public function postSettingIndex(Request $request, $accountId, $isAdmin)
    {

        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);
        $Setting = new getSettingVendorController($accountId);

        $Client = new UdsClient($request->companyId, $request->TokenUDS);
        $body = $Client->getisErrors("https://api.uds.app/partner/v2/settings");
        if ($body == 200){
            $this->Setting_Main_Create_Or_Update( $accountId, $Setting->TokenMoySklad, $request->companyId, $request->TokenUDS, $request->ProductFolder, $request->UpdateProduct, $request->Store,);
            $this->ProductFolderSettingCreateOrUpdate($request, $Setting);
            $this->CreateWebhookByProductMS($request, $Setting);
            $this->CreateWebhookStockByProductMS($Setting);
            $app->companyId = $request->companyId; $app->TokenUDS = $request->TokenUDS;
            $app->ProductFolder = $request->ProductFolder; $app->UpdateProduct = $request->UpdateProduct; $app->Store = $request->Store;
            $app->status = AppInstanceContoller::ACTIVATED;
            app(VendorApiController::class)->updateAppStatus($appId, $accountId, $app->getStatusName());

            $app->persist();
            $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
            $message["message"] = "Настройки сохранились!";
        } else {
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Не верный ID Компании или API Key";
        }

        return  redirect()->route('indexSetting', [
            'message' => $message,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function postSettingOrder(Request $request, $accountId, $isAdmin){
        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);

        $Organization = $request->Organization;
        if ('Нет расчетного счёта' != $request->$Organization){
            $PaymentAccount = $request->$Organization;
        } else $PaymentAccount = null;


        if ($request->creatDocument == "1"){
            $app->creatDocument = $request->creatDocument;
            $app->Organization = $request->Organization;
            $app->Document = $request->Document;
            $app->PaymentDocument = $request->PaymentDocument;
            if ($request->PaymentDocument == "2") $app->PaymentAccount = $PaymentAccount;  else { $app->PaymentAccount = null; $PaymentAccount = null; }
            if ($request->Saleschannel == "0") { $app->Saleschannel = null; } else { $app->Saleschannel = $request->Saleschannel; }
            if ($request->Project == "0") { $app->Project = null; } else { $app->Project = $request->Project; }
            if ($request->NEW == 'Статус МойСклад') { $app->NEW = null; } else { $app->NEW = $request->NEW; }
            if ($request->COMPLETED == 'Статус МойСклад') { $app->COMPLETED = null; } else { $app->COMPLETED = $request->COMPLETED; }
            if ($request->DELETED == 'Статус МойСклад') { $app->DELETED = null; } else { $app->DELETED = $request->DELETED; }
            $app->persist();

            orderSettingModel::create([
                'accountId' => $accountId,
                'creatDocument' => $request->creatDocument,
                'Organization' => $request->Organization,
                'PaymentDocument' => $request->PaymentDocument,
                'Document' => $request->Document,
                'PaymentAccount' => $PaymentAccount,
            ]);
            $message = "Настройки сохранились!";
            $status = 200;
        } else {
            $app->creatDocument = null;
            $app->Organization = null;
            $app->Document = null;
            $app->PaymentDocument = null;
            $app->PaymentAccount = null;

            $app->Saleschannel = null;
            $app->Project = null;
            $app->NEW = null;
            $app->COMPLETED = null;
            $app->DELETED = null;

            $app->persist();

        }

        $message = "Настройки сохранились!";

        return redirect()->route('indexDocument', [
            'message' => $message,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);

    }



    private function Setting_Main_Create_Or_Update($accountId, $TokenMS, mixed $companyId, mixed $TokenUDS, mixed $ProductFolder, mixed $UpdateProduct, mixed $Store)
    {
        $Create = new create();
        $update = new update();
        $Setting =  SettingMain::query()->where('accountId', $accountId)->get()->all();

        if ($Setting == []) { $Create->SettingMainCreate(
            $accountId,
            $TokenMS,
            $companyId,
            $TokenUDS,
            $ProductFolder,
            $UpdateProduct,
            $Store,
        );
        } else {
            $update->SettingMainUpdate( $accountId, $TokenMS,
            $companyId,
            $TokenUDS,
            $ProductFolder,
            $UpdateProduct,
            $Store,
            );
        }
    }

    private function ProductFolderSettingCreateOrUpdate(Request $request, getSettingVendorController $Setting)
    {
        $Client = new MsClient($Setting->TokenMoySklad);
        $find = ProductFoldersByAccountID::query()->where('accountId', $Setting->accountId);
        $find->delete();

        foreach ($request->all() as $item){
            if (mb_substr($item, 0, 6) == "Folder"){
                $id = mb_substr($item, 6, 120);
                if ($id == "0") {
                    $FolderName = "Корневая папка";
                    $FolderID = "0";
                    $FolderURLs = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder";
                } else {
                    $body = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$id);
                    $FolderName = $body->name;
                    $FolderID = $body->id;
                    $FolderURLs = $body->meta->href;
                }
                ProductFoldersByAccountID::create([
                    'accountId' => $Setting->accountId,
                    'FolderName' => $FolderName,
                    'FolderID' => $FolderID,
                    'FolderURLs' => $FolderURLs,
                ]);
            }
        }

    }

    private function CreateWebhookByProductMS(Request $request, getSettingVendorController $Setting)
    {

        $Client = new MsClient($Setting->TokenMoySklad);
        $Webhook_check = true;
        $Webhook_body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/webhook/')->rows;
        if ($Webhook_body != []){
            foreach ($Webhook_body as $item){
                if ($item->url == "https://dev.smartuds.kz/api/webhook/product/"){
                    $Webhook_check = false;
                }
            }
        }
        if ($Webhook_check) {
            $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook/', [
                'url' => "https://dev.smartuds.kz/api/webhook/product/",
                'action' => "UPDATE",
                'entityType' => "product",
            ]);
        }

        if ($Webhook_body != []){
            foreach ($Webhook_body as $item){
                if ($item->url == "https://dev.smartuds.kz/api/webhook/productfolder/"){
                    $Webhook_check = false;
                }
            }
        }
        if ($Webhook_check) {
            $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook/', [
                'url' => "https://dev.smartuds.kz/api/webhook/productfolder/",
                'action' => "UPDATE",
                'entityType' => "productfolder",
            ]);
        }

    }

    private function CreateWebhookStockByProductMS(getSettingVendorController $Setting)
    {

        $Client = new MsClient($Setting->TokenMoySklad);
        $Webhook_check = true;
        $WebhookID = 0;
        $Webhook_body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/webhookstock')->rows;
        if ($Webhook_body != []){
            foreach ($Webhook_body as $item){
                if ($item->url == "https://dev.smartuds.kz/api/webhook/stock/"){
                    $Webhook_check = false;
                    $WebhookID = $item->id;
                }
            }
        }
        if ($Webhook_check) {
            $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhookstock', [
                'url' => "https://dev.smartuds.kz/api/webhook/stock/",
                'enabled' => "true",
                'reportType' => "bystore",
                'stockType' => "stock",
            ]);
        }
        if ($WebhookID != 0) {
            $Client->put('https://online.moysklad.ru/api/remap/1.2/entity/webhookstock/'.$WebhookID, [
                'url' => "https://dev.smartuds.kz/api/webhook/stock/",
                'enabled' => "true",
                'reportType' => "bystore",
                'stockType' => "stock",
            ]);
        }
    }
}
