<?php

namespace App\Http\Controllers\Web;

use App\Components\MsClient;
use App\Components\UdsClient;
use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\getData\getSetting;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Models\counterparty_add;
use App\Models\orderSettingModel;
use App\Models\SettingMain;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SettingController extends Controller
{

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
        $url_customerorder = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
        $url_saleschannel = "https://api.moysklad.ru/api/remap/1.2/entity/saleschannel";
        $url_project = "https://api.moysklad.ru/api/remap/1.2/entity/project";
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

            "message" => $request->message ?? '',
            "class_message" => $request->class_message ?? 'is-info',

            'isAdmin' => $isAdmin,
        ]);
    }

    public function postSettingAdd(Request $request, $accountId, $isAdmin){

        $cfg = new cfg();
        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($accountId);

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
        $url_customerorder = "https://api.moysklad.ru/api/remap/1.2/entity/customerorder/metadata";
        $url_saleschannel = "https://api.moysklad.ru/api/remap/1.2/entity/saleschannel";
        $url_project = "https://api.moysklad.ru/api/remap/1.2/entity/project";
        $responses = Http::withToken($TokenMoySklad)->pool(fn (Pool $pool) =>
        [
            $pool->as('body_customerorder')->withToken($TokenMoySklad)->get($url_customerorder),
            $pool->as('body_saleschannel')->withToken($TokenMoySklad)->get($url_saleschannel),
            $pool->as('body_project')->withToken($TokenMoySklad)->get($url_project),
        ]);

        $class_message = "is-success";
        $message = "Настройки сохранились!";

        return view('web.Setting.documentAdd',[
            "Body_customerorder" => $responses['body_customerorder']->object()->states,
            "Body_saleschannel" => $responses['body_saleschannel']->object()->rows,
            "Body_project" => $responses['body_project']->object()->rows,

            "Saleschannel" => $request->Saleschannel,
            "Project" => $request->Project,

            "NEW" => $request->NEW,
            "COMPLETED" => $request->COMPLETED,
            "DELETED" => $request->DELETED,

            'message' => $message,
            'class_message' => $class_message,

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


}
