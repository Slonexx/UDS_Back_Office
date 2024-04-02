<?php

namespace App\Http\Controllers\Web\Setting;

use App\Components\MsClient;
use App\Http\Controllers\BD\getMainSettingBD;
use App\Http\Controllers\BD\newProductSettingBD;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Models\newProductModel;
use App\Models\ProductFoldersByAccountID;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class productController extends Controller
{
    public function indexProduct(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($isAdmin == "NO") {
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin]);
        }

        $Setting = new getSettingVendorController($accountId);
        $SettingBD = new getMainSettingBD($accountId);
        $newSetting = new newProductSettingBD($accountId);

        $companyId = $SettingBD->companyId;
        $TokenUDS = $SettingBD->TokenUDS;
        $ClientMs = new MsClient($Setting->TokenMoySklad);

        $arrFolders = [];

        /*
         * 1)Категории
         * 2)Склад
         * 3)Цена
        */

        $arrayProductFolders = $ClientMs->get('https://api.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName=')->rows;
        $arrayStores = $ClientMs->get('https://api.moysklad.ru/api/remap/1.2/entity/store')->rows;
        $arrayPrice = [
            'salesPrices' => $ClientMs->get('https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype'),
            'promotionalPrice' => $ClientMs->get('https://api.moysklad.ru/api/remap/1.2/context/companysettings/pricetype'),
        ];


        array_unshift($arrayProductFolders, json_decode(json_encode(['id' => '0', 'name' => 'Корневая папка'])));


    if ($newSetting->ProductFolder == null or  $newSetting->ProductFolder == '0'){
        $ProductFolder = '0';
        $unloading = '0';
        $salesPrices = '';
        $promotionalPrice = '';
        $Store = '';
        $StoreRecord = '0';
        $productHidden = '0';
    } else {
        $ProductFolder = $newSetting->ProductFolder ?? '0';
        $unloading = $newSetting->unloading ?? '0';
        $salesPrices = $newSetting->salesPrices ?? '';
        $promotionalPrice = $newSetting->promotionalPrice ?? '';
        $Store = $newSetting->Store ?? '';
        $StoreRecord = $newSetting->StoreRecord ?? '0';
        $productHidden = $newSetting->productHidden ?? '0';

        if ($newSetting->ProductFolder == "1") {
            foreach (ProductFoldersByAccountID::query()->where('accountId', $Setting->accountId)->get() as $index => $item) {
                $arrFolders[$index] = [
                    'id' => $item->getModel()->getAttributes()['FolderID'],
                    'Name' => $item->getModel()->getAttributes()['FolderName']
                ];
            }
        }
    }



        return view('web.Setting.Product.product', [
            "Body_productFolder" => $arrayProductFolders,
            "Body_store" => $arrayStores,
            "Body_Price" => $arrayPrice,

            "ProductFolder" => $ProductFolder,
            "unloading" => $unloading,
            "salesPrices" => $salesPrices,
            "promotionalPrice" => $promotionalPrice,
            "Store" => $Store,
            "StoreRecord" => $StoreRecord,
            "productHidden" => $productHidden,

            "Folders" => $arrFolders,

            "companyId" => $companyId,
            "TokenUDS" => $TokenUDS,

            "accountId" => $accountId,


            "message" => $request->message ?? '',
            "class_message" => $request->class_message ?? 'is-info',

            "isAdmin" => $isAdmin,
        ]);
    }


    public function postProduct(Request $request, $accountId, $isAdmin): \Illuminate\Http\RedirectResponse
    {
        $Setting = new getSettingVendorController($accountId);
        $ClientMS = new MsClient($Setting->TokenMoySklad);

        $this->createNewProductModel($accountId, $request);
        $this->ProductFolderSettingCreateOrUpdate($request, $Setting);

        $this->CreateWebhookByProductMS($request, $ClientMS);
        $this->CreateWebhookStockByProductMS($request, $ClientMS);

        $class_message = "is-success";
        $message = "Настройки сохранились!";

        return redirect()->route('productIndex', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            'message' => $message,
            'class_message' => $class_message,
            ]);

    }

    private function messageRequest(mixed $message): array
    {
        if ($message) {
            return [
                'status' => true,
                'message' => $message['message'],
                'alert' => $message['alert'],
            ];
        } else return [
            'status' => false,
            'message' => "",
            'alert' => "",
        ];
    }



    private function createNewProductModel($accountId, $request): void
    {
        $model = new newProductModel();
        $existingRecords = newProductModel::where('accountId', $accountId)->get();

        if (!$existingRecords->isEmpty()) { foreach ($existingRecords as $record) { $record->delete(); } }

        $model->accountId = $accountId;
        $model->ProductFolder = $request->ProductFolder;

        if ($request->ProductFolder == '0'){
            $model->unloading = null;
            $model->salesPrices = null;
            $model->promotionalPrice = null;
            $model->Store = null;
            $model->StoreRecord = null;
            $model->productHidden = null;
            $model->countRound = null;
        } else {
            $model->unloading = $request->unloading ?? '';
            $model->salesPrices = $request->salesPrices ?? '';
            $model->promotionalPrice = $request->promotionalPrice ?? '';
            $model->Store = $request->Store ?? '';
            $model->StoreRecord = $request->StoreRecord ?? '';
            $model->productHidden = $request->productHidden ?? '';
            $model->countRound = 0;
        }

        $model->save();
    }

    private function ProductFolderSettingCreateOrUpdate(Request $request, getSettingVendorController $Setting): void
    {
        $Client = new MsClient($Setting->TokenMoySklad);
        $find = ProductFoldersByAccountID::query()->where('accountId', $Setting->accountId);
        $find->delete();

        foreach ($request->all() as $item) {
            if (mb_substr($item, 0, 6) == "Folder") {
                $id = mb_substr($item, 6, 120);
                if ($id == "0") {
                    $FolderName = "Корневая папка";
                    $FolderID = "0";
                    $FolderURLs = "https://api.moysklad.ru/api/remap/1.2/entity/productfolder";
                } else {
                    $body = $Client->get("https://api.moysklad.ru/api/remap/1.2/entity/productfolder/" . $id);
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



    private function CreateWebhookByProductMS(Request $request, MsClient $msClient): void
    {
        $ProductFolder = 0;
        if ($request->ProductFolder == "1") {
            $ProductFolder = 1;
        }

        $WebhookProduct = true;
        $WebhookProductFolder = true;
        $Webhook_body = $msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/webhook/')->rows;
        if ($Webhook_body != []) {
            foreach ($Webhook_body as $item) {
                if ($item->url == "https://smartuds.kz/api/webhook/product") {
                    $WebhookProduct = false;
                    if ($ProductFolder != 1) $msClient->delete('https://api.moysklad.ru/api/remap/1.2/entity/webhook/' . $item->id, null);
                }

                if ($item->url == "https://smartuds.kz/api/webhook/productfolder") {
                    $WebhookProductFolder = false;
                    if ($ProductFolder != 1) $msClient->delete('https://api.moysklad.ru/api/remap/1.2/entity/webhook/' . $item->id, null);
                }
            }
        }
        if ($WebhookProduct and $ProductFolder == 1) {
            $msClient->post('https://api.moysklad.ru/api/remap/1.2/entity/webhook/', [
                'url' => "https://smartuds.kz/api/webhook/product",
                'action' => "UPDATE",
                'diffType' => "FIELDS",
                'entityType' => "product",
            ]);
        }
        if ($WebhookProductFolder and $ProductFolder == 1) {
            $msClient->post('https://api.moysklad.ru/api/remap/1.2/entity/webhook/', [
                'url' => "https://smartuds.kz/api/webhook/productfolder",
                'action' => "UPDATE",
                'diffType' => "FIELDS",
                'entityType' => "productfolder",
            ]);
        }

    }
    private function CreateWebhookStockByProductMS(Request $request, MsClient $msClient): void
    {
        $ProductFolder = 0;
        if ($request->ProductFolder == "1") {
            $ProductFolder = 1;
        }
        $body = [
            'url' => "https://smartuds.kz/api/webhook/stock",
            'enabled' => true,
            'reportType' => "bystore",
            'stockType' => "stock",
        ];
        $Webhook_check = true;

        $Webhook_body = $msClient->get('https://api.moysklad.ru/api/remap/1.2/entity/webhookstock')->rows;
        if ($Webhook_body != []) {
            foreach ($Webhook_body as $item) {
                if ($item->url == "https://smartuds.kz/api/webhook/stock") {
                    $Webhook_check = false;

                    if ($ProductFolder != 1) {
                        $msClient->delete('https://api.moysklad.ru/api/remap/1.2/entity/webhookstock/' . $item->id, []);
                        $Webhook_check = true;
                    }
                }
            }
        }
        if ($Webhook_check and $ProductFolder == 1) $msClient->post('https://api.moysklad.ru/api/remap/1.2/entity/webhookstock', $body);
    }
}
