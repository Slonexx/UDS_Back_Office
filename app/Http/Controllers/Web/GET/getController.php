<?php

namespace App\Http\Controllers\Web\GET;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Controller;
use App\Models\ProductFoldersByAccountID;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class getController extends Controller
{

    public function mainSetting(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($isAdmin == "NO"){ return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin] ); }

        $Setting = new getSettingVendorController($accountId);

        $TokenMoySklad = $Setting->TokenMoySklad;
        $companyId = $Setting->companyId;
        $TokenUDS = $Setting->TokenUDS;
        $ProductFolder = $Setting->ProductFolder;
        $Store = $Setting->Store;
        $arrFolders = [];

        if (isset($request->message)){
        $message = [
            'status'=> true,
            'message'=> $request->message['message'],
            'alert'=> $request->message['alert'],
        ];
        } else $message = [
            'status'=> false,
            'message'=> "",
            'alert'=> "",
        ];


        if ($ProductFolder != null) {
            if ($ProductFolder == "1"){
                $Folders = ProductFoldersByAccountID::query()->where('accountId', $Setting->accountId)->get();
                foreach ($Folders as $index=>$item){
                    $arrFolders[$index] = [
                        'id'=> $item->getModel()->getAttributes()['FolderID'],
                        'Name'=> $item->getModel()->getAttributes()['FolderName']
                    ];
                }
            } else {
                $ProductFolder = "0";
            }
        } else $ProductFolder = "0";
        if ($Store == null) $Store = "0";

        $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
            $pool->as('body_store')->withToken($TokenMoySklad)->get("https://online.moysklad.ru/api/remap/1.2/entity/store"),
            $pool->as('body_productFolder')->withToken($TokenMoySklad)->get("https://online.moysklad.ru/api/remap/1.2/entity/productfolder?filter=pathName="),
        ]);

        $body_productFolder[] = json_decode(json_encode(['id' => '0', 'name'=>'Корневая папка' ]));
        if (array_key_exists(0,$responses['body_productFolder']->object()->rows)){
            foreach ($responses['body_productFolder']->object()->rows as $item){
                $body_productFolder[] = $item;
            }
        }

        return view('web.Setting.index', [
            "Body_store" => $responses['body_store']->object()->rows,
            "Body_productFolder" => $body_productFolder,
            "Folders" => $arrFolders,

            "companyId"=> $companyId,
            "TokenUDS"=> $TokenUDS,

            "ProductFolder" => $ProductFolder,
            "Store" => $Store,

            "accountId"=> $accountId,
            "message"=> $message,
            "isAdmin" => $isAdmin,
        ]);
    }



    public function indexDocument(Request $request, $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {

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
        $message = '0';
        if (isset($request->message)){ $message = $request->message; }

        $Client = new MsClient($Setting->TokenMoySklad);
        $TokenMoySklad = $Setting->TokenMoySklad;
        $creatDocument = $Setting->creatDocument;
        $Organization = $Setting->Organization;
        $PaymentDocument = $Setting->PaymentDocument;
        $Document = $Setting->Document;
        $PaymentAccount = $Setting->PaymentAccount;

        $Saleschannel = $Setting->Saleschannel;
        $Project = $Setting->Project;
        $NEW = $Setting->NEW;
        $COMPLETED = $Setting->COMPLETED;
        $DELETED = $Setting->DELETED;

        if ($creatDocument == null) $creatDocument = "0";
        if ($PaymentDocument == null) $PaymentDocument = "0";
        if ($Document == null) $Document = "0";
        if ($PaymentAccount == null) $PaymentAccount = "0";

        if ($Saleschannel == null) $Saleschannel = "0";
        if ($Project == null) $Project = "0";
        if ($NEW == null) $NEW = "0";
        if ($COMPLETED == null) $COMPLETED = "0";
        if ($DELETED == null) $DELETED = "0";

        //dd($Setting);

        $url_organization = "https://online.moysklad.ru/api/remap/1.2/entity/organization";
        $url_customerorder = "https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
        $url_saleschannel = "https://online.moysklad.ru/api/remap/1.2/entity/saleschannel";
        $url_project = "https://online.moysklad.ru/api/remap/1.2/entity/project";

        if($Organization != null){
            $urlCheck = $url_organization . "/" . $Organization;
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('organization')->withToken($TokenMoySklad)->get($urlCheck),
                $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),

                $pool->as('body_customerorder')->withToken($TokenMoySklad)->get($url_customerorder),
                $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
                $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),
            ]);
            $Organization = $responses['organization']->object();
        } else {
            $Organization = "0";
            $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) => [
                $pool->as('body_organization')->withToken($TokenMoySklad)->get($url_organization),

                $pool->as('body_customerorder')->withToken($TokenMoySklad)->get($url_customerorder),
                $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
                $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),
            ]);
        }

        $arr_Organization = $responses['body_organization']->object()->rows;

        $arr_PaymentAccount = [];
        foreach ($arr_Organization as $item){
            $arr_PaymentAccount[$item->id] = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/organization/".$item->id."/accounts")->rows;
        }

        return view('web.Setting.document', [

            "arr_Organization" => $arr_Organization,
            "arr_PaymentAccount" => $arr_PaymentAccount,

            "arr_Customerorder" => $responses['body_customerorder']->object()->states,
            "arr_Saleschannel" => $responses['body_saleschannel']->object()->rows,
            "arr_Project" => $responses['body_project']->object()->rows,

            "creatDocument" => $creatDocument,
            "Organization" => $Organization,
            "PaymentDocument" => $PaymentDocument,
            "Document" => $Document,
            "PaymentAccount" => $PaymentAccount,

            "Saleschannel" => $Saleschannel,
            "Project" => $Project,
            "NEW" => $NEW,
            "COMPLETED" => $COMPLETED,
            "DELETED" => $DELETED,

            "message" => $message,
            "apiKey" => $TokenMoySklad,
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }

}
