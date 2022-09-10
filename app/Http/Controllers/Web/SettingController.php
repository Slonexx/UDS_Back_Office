<?php

namespace App\Http\Controllers\Web;

use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Models\counterparty_add;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index(Request $request, $accountId, $isAdmin){
        if ($isAdmin == "NO"){
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin] );
        }

        $Setting = new getSettingVendorController($accountId);
        $TokenMoySklad = $Setting->TokenMoySklad;

        $companyId = $Setting->companyId;
        $TokenUDS = $Setting->TokenUDS;

        $url_productFolder = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName=";
        $ProductFolder = $Setting->ProductFolder;
        if ($Setting->ProductFolder != null) {
            try {
                $urlFolder = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$Setting->ProductFolder;
                $ClientFolder = new ClientMC($urlFolder, $TokenMoySklad);
                $FolderName = $ClientFolder->requestGet()->name;

                $ProductFolder = ['value' => $Setting->ProductFolder, 'name'=>$FolderName ];
            } catch (ClientException $exception) {
                $ProductFolder = null;
                $cfg = new cfg();
                $app = AppInstanceContoller::loadApp($cfg->appId, $accountId);
                $app->ProductFolder = null;
                $app->persist();
            }

        }

        $url_store = "https://online.moysklad.ru/api/remap/1.2/entity/store";
        $Store = $Setting->Store;
        if ($Store == null) $Store = "0";


        $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
            $pool->as('body_store')->withToken($TokenMoySklad)->get($url_store),
            $pool->as('body_productFolder')->withToken($TokenMoySklad)->get($url_productFolder),
        ]);


        return view('web.Setting.index', [
            "Body_store" => $responses['body_store']->object()->rows,
            "Body_productFolder" => $responses['body_productFolder']->object()->rows,

            "ProductFolder" => $ProductFolder,
            "Store" => $Store,
            "accountId"=> $accountId,
            "isAdmin" => $isAdmin,

            "companyId"=> $companyId,
            "TokenUDS"=> $TokenUDS,
        ]);
    }

    public function postSettingIndex(Request $request, $accountId, $isAdmin){
        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);

        $TokenMoySklad = $app->TokenMoySklad;
        $url_store = "https://online.moysklad.ru/api/remap/1.2/entity/store";
        $url_productFolder = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName=";
        $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
            $pool->as('body_store')->withToken($TokenMoySklad)->get($url_store),
            $pool->as('body_productFolder')->withToken($TokenMoySklad)->get($url_productFolder),
        ]);

        $urlFolder = "https://online.moysklad.ru/api/remap/1.2/entity/productfolder/".$request->ProductFolder;
        $ClientFolder = new ClientMC($urlFolder, $TokenMoySklad);
        $FolderName = $ClientFolder->requestGet()->name;
        $ProductFolder = ['value' => $request->ProductFolder, 'name'=>$FolderName ];

        $Client = new UdsClient($request->companyId, $request->TokenUDS);
        $body = $Client->getisErrors("https://api.uds.app/partner/v2/settings");
        if ($body == 200){
            $app->companyId = $request->companyId;
            $app->TokenUDS = $request->TokenUDS;

            $app->ProductFolder = $request->ProductFolder;
            $app->UpdateProduct = $request->UpdateProduct;
            $app->Store = $request->Store;
            $app->status = AppInstanceContoller::ACTIVATED;

            $vendorAPI = new VendorApiController();
            $vendorAPI->updateAppStatus($appId, $accountId, $app->getStatusName());

            $BD = new BDController();
            $BD->createCounterparty($accountId, $TokenMoySklad, $request->companyId,  $request->TokenUDS);

            $app->persist();

            $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
            $message["message"] = "Настройки сохранились!";
        } else {
            $message["alert"] = " alert alert-danger alert-dismissible fade show in text-center ";
            $message["message"] = "Не верный ID Компании или API Key";
        }

        return view('web.Setting.index', [
            "Body_store" => $responses['body_store']->object()->rows,
            "Body_productFolder" => $responses['body_productFolder']->object()->rows,

            "ProductFolder" => $ProductFolder,
            "Store" => $request->Store,

            "companyId"=> $request->companyId,
            "TokenUDS"=> $request->TokenUDS,

            "message" => $message,
            "accountId"=> $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }


    public function indexDocument(Request $request, $accountId, $isAdmin){
        if ($isAdmin == "NO"){
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin] );
        }

        $Setting = new getSettingVendorController($accountId);
        $companyId = $Setting->companyId;
        if ( $companyId == null ) {
            $message = " Основные настройки не были установлены ";
            return redirect()->route('indexError', [
                "accountId" => $accountId,
                "isAdmin" => $isAdmin,
                "message" => $message,
                ]);
        }

        $TokenMoySklad = $Setting->TokenMoySklad;
        $creatDocument = $Setting->creatDocument;
        $Organization = $Setting->Organization;
        $PaymentDocument = $Setting->PaymentDocument;
        $Document = $Setting->Document;
        $PaymentAccount = $Setting->PaymentAccount;

        if ($PaymentDocument == null) $PaymentDocument = "0";
        if ($Document == null) $Document = "0";
        if ($PaymentAccount == null) $PaymentAccount = "0";

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
        $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/organization";

        if($Organization != null){
            $urlCheck = $url_organization . "/" . $Organization;
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body')->withToken($TokenMoySklad)->get($url),
                $pool->as('organization')->withToken($TokenMoySklad)->get($urlCheck),
                $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),
            ]);
            $Organization = $responses['organization']->object();
        } else {
            $Organization = "0";
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body')->withToken($TokenMoySklad)->get($url),
                $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),
            ]);
        }

        return view('web.Setting.document', [
            'Body' => $responses['body']->object()->states,
            "Body_organization" => $responses['body_organization']->object()->rows,

            "creatDocument" => $creatDocument,
            "Organization" => $Organization,
            "PaymentDocument" => $PaymentDocument,
            "Document" => $Document,
            "PaymentAccount" => $PaymentAccount,

            "apiKey" => $TokenMoySklad,
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function postSettingDocument(Request $request, $accountId, $isAdmin){

        $creatDocument = $request->creatDocument;
        $Organization = $request->Organization;

        if ('Нет расчетного счёта' != $request->$Organization){
            $PaymentAccount = $request->$Organization;
        } else $PaymentAccount = null;


        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);

            $TokenMoySklad = $app->TokenMoySklad;
            $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
            $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/organization";

                $urlCheck = $url_organization . "/" . $request->Organization;
                $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                    $pool->as('body')->withToken($TokenMoySklad)->get($url),
                    $pool->as('organization')->withToken($TokenMoySklad)->get($urlCheck),
                    $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),
                ]);
                $Organization = $responses['organization']->object();



        if ($creatDocument == "1"){

            $app->creatDocument = $request->creatDocument;
            $app->Organization = $request->Organization;
            $app->Document = $request->Document;
            $app->PaymentDocument = $request->PaymentDocument;
            if ($request->PaymentDocument == "2") $app->PaymentAccount = $PaymentAccount;
            else {
                $app->PaymentAccount = null;
                $PaymentAccount = null;
            }

            $app->persist();

        } else {
            $app->creatDocument = null;
            $app->Organization = null;
            $app->Document = null;
            $app->PaymentDocument = null;
            $app->PaymentAccount = null;

            $app->persist();
        }

        $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
        $message["message"] = "Настройки сохранились!";

        return view('web.Setting.document', [
            'Body' => $responses['body']->object()->states,
            "Body_organization" => $responses['body_organization']->object()->rows,

            "creatDocument" => $request->creatDocument,
            "Organization" => $Organization,
            "PaymentDocument" =>  $request->PaymentDocument,
            "Document" =>  $request->Document,
            "PaymentAccount" =>  $PaymentAccount,

            "message" => $message,
            "apiKey" => $TokenMoySklad,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);

    }


    public function indexAdd(Request $request, $accountId, $isAdmin){
        if ($isAdmin == "NO"){
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin] );
        }
        $Setting = new getSettingVendorController($accountId);

        $companyId = $Setting->companyId;
        if ( $companyId == null ) {
            $message = " Основные настройки не были установлены ";

            return redirect()->route('indexError', [
                "accountId" => $accountId,
                "isAdmin" => $isAdmin,
                "message" => $message,
            ]);
        }



        $Saleschannel = $Setting->Saleschannel;
        $Project = $Setting->Project;

        if ($Saleschannel == null) $Saleschannel = "0";
        if ($Project == null) $Project = "0";

        $NEW = $Setting->NEW;
        $COMPLETED = $Setting->COMPLETED;
        $DELETED = $Setting->DELETED;

            $TokenMoySklad = $Setting->TokenMoySklad;
            $url_customerorder = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
            $url_saleschannel = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel";
            $url_project = "https://online.moysklad.ru/api/remap/1.2/entity/project";
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) =>
            [
                $pool->as('body_customerorder')->withToken($TokenMoySklad)->get($url_customerorder),
                $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
                $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),
            ]);



        return view('web.Setting.documentAdd',[
            "Body_customerorder" => $responses['body_customerorder']->object()->states,
            "Body_saleschannel" => $responses['body_saleschannel']->object()->rows,
            "Body_project" => $responses['body_project']->object()->rows,

            "Saleschannel" => $Saleschannel,
            "Project" => $Project,

            "NEW" => $NEW,
            "COMPLETED" => $COMPLETED,
            "DELETED" => $DELETED,

            "accountId"=> $accountId,
            'isAdmin' => $isAdmin,
            ]);
    }

    public function postSettingAdd(Request $request, $accountId, $isAdmin){

        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);

        $Saleschannel = $request->Saleschannel;
        $Project = $request->Project;

        if ($Saleschannel == "0") $Saleschannel = null;
        if ($Project == "0") $Project = null;

        $app->Saleschannel = $Saleschannel;
        $app->Project = $Project;

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

        $message["alert"] = " alert alert-success alert-dismissible fade show in text-center ";
        $message["message"] = "Настройки сохранились!";

            $TokenMoySklad = $app->TokenMoySklad;
            $url_customerorder = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
            $url_saleschannel = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel";
            $url_project = "https://online.moysklad.ru/api/remap/1.2/entity/project";
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) =>
            [
                $pool->as('body_customerorder')->withToken($TokenMoySklad)->get($url_customerorder),
                $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
                $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),
            ]);

        return view('web.Setting.documentAdd',[
            "Body_customerorder" => $responses['body_customerorder']->object()->states,
            "Body_saleschannel" => $responses['body_saleschannel']->object()->rows,
            "Body_project" => $responses['body_project']->object()->rows,

            "Saleschannel" => $request->Saleschannel,
            "Project" => $request->Project,

            "NEW" => $request->NEW,
            "COMPLETED" => $request->COMPLETED,
            "DELETED" => $request->DELETED,

            "message"=> $message,
            "accountId"=> $accountId,
            'isAdmin' => $isAdmin,
        ]);

    }


    public function indexError($accountId, $isAdmin, $message){

        return view('web.Setting.errorSetting',[
            "accountId"=> $accountId,
            'isAdmin' => $isAdmin,
            "message"=> $message,
        ]);

    }

    public function indexNoAdmin($accountId, $isAdmin){
        return view('web.Setting.noAdmin',[
            "accountId"=> $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

    public function CountProduct($accountId, $folderName){
        try {
            $Setting = new getSettingVendorController($accountId);
            $url = 'https://online.moysklad.ru/api/remap/1.2/entity/product?filter=pathName=';

            $Client = new ClientMC($url.$folderName, $Setting->TokenMoySklad);
            $Body = $Client->requestGet()->meta;
            $result = [
                'StatusCode' => "200",
                'Body' => $Body->size,
            ];
        } catch (ClientException $exception){
            $result = [
                'StatusCode' => $exception->getCode(),
                'Body' => $exception->getMessage(),
            ];
        }
        return $result;
    }
}
