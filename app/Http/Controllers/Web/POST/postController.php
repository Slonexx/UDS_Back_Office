<?php

namespace App\Http\Controllers\Web\POST;

use App\Components\MsClient;
use App\Components\UdsClient;
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
use Illuminate\Http\Request;

class postController extends Controller
{
    public function postSettingIndex(Request $request, $accountId, $isAdmin): \Illuminate\Http\RedirectResponse
    {
        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);
        $Setting = new getSettingVendorController($accountId);

        $Client = new UdsClient($request->companyId, $request->TokenUDS);
        $body = $Client->getisErrors("https://api.uds.app/partner/v2/settings");
        if ($body == 200) {
            $this->Setting_Main_Create_Or_Update($accountId, $Setting->TokenMoySklad, $request->companyId, $request->TokenUDS);
            $app->companyId = $request->companyId;
            $app->TokenUDS = $request->TokenUDS;
            $app->ProductFolder = null;
            $app->UpdateProduct = null;
            $app->Store = null;
            $app->status = AppInstanceContoller::ACTIVATED;
            app(VendorApiController::class)->updateAppStatus($appId, $accountId, $app->getStatusName());

            $app->persist();
            $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
            $message["message"] = "Настройки сохранились!";
        } else {
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Не верный ID Компании или API Key";
        }

        return redirect()->route('indexSetting', [
            'message' => $message,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    private function Setting_Main_Create_Or_Update( string $accountId, string $TokenMS, string $companyId, string $TokenUDS): void
    {
        $Setting = SettingMain::query()->where('accountId', $accountId);

        if ($Setting == []) {
            SettingMain::create([
                'accountId' => $accountId,
                'TokenMoySklad' => $TokenMS,
                'companyId' => $companyId,
                'TokenUDS' => $TokenUDS,
            ]);
        } else {
            $SettingMain_update = SettingMain::query()->where('accountId', $accountId);
            $SettingMain_update->update([
                'TokenMoySklad' => $TokenMS,
                'companyId' => $companyId,
                'TokenUDS' => $TokenUDS,
            ]);
        }
    }

    public function postSettingOrder(Request $request, $accountId, $isAdmin): \Illuminate\Http\RedirectResponse
    {
        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);

        $Organization = $request->Organization;
        if ('Нет расчетного счёта' != $request->$Organization) {
            $PaymentAccount = $request->$Organization;
        } else $PaymentAccount = null;


        if ($request->creatDocument == "1") {
            $app->creatDocument = $request->creatDocument;
            $app->Organization = $request->Organization;
            $app->Document = $request->Document;
            $app->PaymentDocument = $request->PaymentDocument;
            if ($request->PaymentDocument == "2") $app->PaymentAccount = $PaymentAccount; else {
                $app->PaymentAccount = null;
                $PaymentAccount = null;
            }
            if ($request->Saleschannel == "0") {
                $app->Saleschannel = null;
            } else {
                $app->Saleschannel = $request->Saleschannel;
            }
            if ($request->Project == "0") {
                $app->Project = null;
            } else {
                $app->Project = $request->Project;
            }
            if ($request->NEW == 'Статус МойСклад') {
                $app->NEW = null;
            } else {
                $app->NEW = $request->NEW;
            }
            if ($request->COMPLETED == 'Статус МойСклад') {
                $app->COMPLETED = null;
            } else {
                $app->COMPLETED = $request->COMPLETED;
            }
            if ($request->DELETED == 'Статус МойСклад') {
                $app->DELETED = null;
            } else {
                $app->DELETED = $request->DELETED;
            }
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
}
