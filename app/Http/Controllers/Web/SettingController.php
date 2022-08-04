<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{
    public function index(Request $request, $accountId){

        $Setting = new getSettingVendorController($accountId);

        return view('web.Setting.index', [
            "accountId"=> $accountId
        ]);
    }



    public function indexDocument(Request $request, $accountId){

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
        $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/organization";
        $url_store = "https://online.moysklad.ru/api/remap/1.2/entity/store";

        $Setting = new getSettingVendorController($accountId);
        $TokenMoySklad = $Setting->TokenMoySklad;
        $Organization = $Setting->Organization;
        $PaymentDocument = $Setting->PaymentDocument;
        $Document = $Setting->Document;
        $PaymentAccount = $Setting->PaymentAccount;
        $Store = $Setting->Store;

        if ($PaymentDocument == null) $PaymentDocument = "0";
        if ($Document == null) $Document = "0";
        if ($PaymentAccount == null) $PaymentAccount = "0";
        if ($Store == null) $Store = "0";

        if($Organization != null){
            $urlCheck = $url_organization . "/" . $Organization;
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body')->withToken($TokenMoySklad)->get($url),
                $pool->as('organization')->withToken($TokenMoySklad)->get($urlCheck),
                $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),
                $pool->as('body_store')->withToken($TokenMoySklad)->get($url_store),
            ]);
            $Organization = $responses['organization']->object();
        } else {
            $Organization = "0";
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body')->withToken($TokenMoySklad)->get($url),
                $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),
                $pool->as('body_store')->withToken($TokenMoySklad)->get($url_store),
            ]);
        }

        return view('web.Setting.document', [
            'Body' => $responses['body']->object()->states,
            "Body_organization" => $responses['body_organization']->object()->rows,
            "Body_store" => $responses['body_store']->object()->rows,

            "Organization" => $Organization,
            "PaymentDocument" => $PaymentDocument,
            "Document" => $Document,
            "PaymentAccount" => $PaymentAccount,
            "Store" => $Store,

            "apiKey" => $TokenMoySklad,
            'accountId' => $accountId,
        ]);
    }

    public function indexAdd(Request $request, $accountId){

        $Setting = new getSettingVendorController($accountId);
        $TokenMoySklad = $Setting->TokenMoySklad;

        $Saleschannel = $Setting->Saleschannel;
        $Project = $Setting->Project;

        if ($Saleschannel == null) $Saleschannel = "0";
        if ($Project == null) $Project = "0";

        $NEW = $Setting->NEW;
        $COMPLETED = $Setting->COMPLETED;
        $DELETED = $Setting->DELETED;
        $WAITING_PAYMENT = $Setting->WAITING_PAYMENT;

        $Organization = $Setting->Organization; //ПРОВЕРКА НА НАСТРОЙКИ ВЫШЕ

        $url_saleschannel = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel";
        $url_project = "https://online.moysklad.ru/api/remap/1.2/entity/project";

            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
                $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),

            ]);



        return view('web.Setting.documentAdd',[
            "Body_saleschannel" => $responses['body_saleschannel']->object()->rows,
            "Body_project" => $responses['body_project']->object()->rows,

            "Saleschannel" => $Saleschannel,
            "Project" => $Project,

            "NEW" => $NEW,
            "COMPLETED" => $COMPLETED,
            "DELETED" => $DELETED,
            "WAITING_PAYMENT" => $WAITING_PAYMENT,

            "accountId"=> $accountId
            ]);
    }
}
