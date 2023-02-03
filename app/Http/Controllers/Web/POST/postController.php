<?php

namespace App\Http\Controllers\Web\POST;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\BD\create;
use App\Http\Controllers\BD\update;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Models\orderSettingModel;
use App\Models\SettingMain;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class postController extends Controller
{
    public function postSettingIndex(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $cfg = new cfg();
        $BD = new BDController();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);


        $TokenMS = $app->TokenMoySklad;
        $Client = new MsClient($TokenMS);
        $url_store = "https://online.moysklad.ru/api/remap/1.2/entity/store";
        $url_productFolder = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName=";
        $urlFolder = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$request->ProductFolder;

        $responses = Http::withToken($TokenMS)->pool(fn (Pool $pool) => [
            $pool->as('body_store')->withToken($TokenMS)->get($url_store),
            $pool->as('body_productFolder')->withToken($TokenMS)->get($url_productFolder),
        ]);
        if ($request->ProductFolder == '0') {
            $ProductFolder = ['value' => $request->ProductFolder, 'name'=>'Корневая папка' ];
            $body_productFolder = $responses['body_productFolder']->object()->rows;
        } else {
            $FolderName = $Client->get($urlFolder)->name;
            $ProductFolder = ['value' => $request->ProductFolder, 'name' => $FolderName ];
            $body_productFolder[] = json_decode(json_encode(['id' => '0', 'name'=>'Корневая папка' ]));
            foreach ($responses['body_productFolder']->object()->rows as $item){
                $body_productFolder[] = $item;
            }
        }

        $Client = new UdsClient($request->companyId, $request->TokenUDS);
        $body = $Client->getisErrors("https://api.uds.app/partner/v2/settings");
        if ($body == 200){
            $this->Setting_Main_Create_Or_Update( $accountId, $TokenMS, $request->companyId, $request->TokenUDS, $request->ProductFolder, $request->UpdateProduct, $request->Store,);

            $app->companyId = $request->companyId; $app->TokenUDS = $request->TokenUDS;
            $app->ProductFolder = $request->ProductFolder; $app->UpdateProduct = $request->UpdateProduct; $app->Store = $request->Store;
            $app->status = AppInstanceContoller::ACTIVATED;
            app(VendorApiController::class)->updateAppStatus($appId, $accountId, $app->getStatusName());

            $BD->createCounterparty($accountId, $TokenMS, $request->companyId,  $request->TokenUDS);
            $app->persist();

            $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
            $message["message"] = "Настройки сохранились!";
        } else {
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Не верный ID Компании или API Key";
        }

        return view('web.Setting.index', [
            "Body_store" => $responses['body_store']->object()->rows,
            "Body_productFolder" => $body_productFolder,

            "ProductFolder" => $ProductFolder,
            "Store" => $request->Store,

            "companyId"=> $request->companyId,
            "TokenUDS"=> $request->TokenUDS,

            "message" => $message,
            "accountId"=> $accountId,
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
        if ($TokenMS == null) { $Create->SettingMainCreate(
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
}
