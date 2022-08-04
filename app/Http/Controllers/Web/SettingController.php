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

        return view('web.Setting.index');
    }



    public function indexDocument(Request $request, $accountId){

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
        $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/organization";
        $url_store = "https://online.moysklad.ru/api/remap/1.2/entity/store";

        $Setting = new getSettingVendorController($accountId);
        $TokenMoySklad = "d86064d4eb4b4a923ff2e679e28774ab63a48c58";
        $TokenKaspi = $Setting->TokenKaspi;

        $Organization = $Setting->Organization;


        $PaymentDocument = $Setting->PaymentDocument;
        if ($PaymentDocument == null) $PaymentDocument = "0";

        $Document = $Setting->Document;
        if ($Document == null) $Document = "0";

        $PaymentAccount = $Setting->PaymentAccount;
        if ($PaymentAccount == null) $PaymentAccount = "0";

        $Store = $Setting->Store;
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

        return view('web.setting.document', ['Body' => $responses['body']->object()->states,
            "Body_organization" => $responses['body_organization']->object()->rows,
            "Body_store" => $responses['body_store']->object()->rows,
            "TokenKaspi" => $TokenKaspi,
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

        if($request->has('error')) {
            if( $request->error != "0" ) $error = $request->error;
            else $error = "0" ;
        }
        else $error = "0" ;

        if($request->has('success')) {
            if( $request->success != "0" ) $success = $request->success;
            else $success = "0" ;
        }
        else $success = "0" ;

        $Setting = new getSettingVendorController($accountId);
        $TokenMoySklad = "d86064d4eb4b4a923ff2e679e28774ab63a48c58";

        $Saleschannel = $Setting->Saleschannel;
        if ($Saleschannel == null) $Saleschannel = "0";

        $Project = $Setting->Project;
        if ($Project == null) $Project = "0";

        $CheckCreatProduct = $Setting->CheckCreatProduct;
        if ($CheckCreatProduct == null) $CheckCreatProduct = "1";

        $Store = $Setting->Store;
        if ($Store == null) $Store = "0";

        $APPROVED_BY_BANK = $Setting->APPROVED_BY_BANK;
        $ACCEPTED_BY_MERCHANT = $Setting->ACCEPTED_BY_MERCHANT;
        $COMPLETED = $Setting->COMPLETED;
        $CANCELLED = $Setting->CANCELLED;
        $RETURNED = $Setting->RETURNED;

        $Organization = $Setting->Organization; //ПРОВЕРКА НА НАСТРОЙКИ ВЫШЕ

        $url_saleschannel = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel";
        $url_project = "https://online.moysklad.ru/api/remap/1.2/entity/project";

            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
                $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),

            ]);



        return view('web.setting.documentAdd',[
            "Body_saleschannel" => $responses['body_saleschannel']->object()->rows,
            "Body_project" => $responses['body_project']->object()->rows,

            "Saleschannel" => $Saleschannel,
            "Project" => $Project,
            "CheckCreatProduct" => $CheckCreatProduct,
            "Store" => $Store,
            "APPROVED_BY_BANK" => $APPROVED_BY_BANK,
            "ACCEPTED_BY_MERCHANT" => $ACCEPTED_BY_MERCHANT,
            "COMPLETED" => $COMPLETED,
            "CANCELLED" => $CANCELLED,
            "RETURNED" => $RETURNED,]);
    }
}
