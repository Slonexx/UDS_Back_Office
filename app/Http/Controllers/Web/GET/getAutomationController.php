<?php

namespace App\Http\Controllers\Web\GET;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getAutomationController extends Controller
{
    public function getAutomation(Request $request,  $accountId, $isAdmin): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        if ($isAdmin == "NO"){
            return redirect()->route('indexNoAdmin', ["accountId" => $accountId, "isAdmin" => $isAdmin] );
        }

        if (isset($request->message)){
            $message = $request->message;
            if ($message == "Настройки сохранились") {
                $class = "mt-1 alert alert-success alert-dismissible fade show in text-center";
            } else $class = "mt-1 alert alert-warning alert-danger fade show in text-center";
        } else {
            $message = '';
            $class = '';
        };

        $Setting = new getSettingVendorController($accountId);
        $Client = new MsClient($Setting->TokenMoySklad);

        $body_meta = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/customerorder/metadata')->states;
        $body_project = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/project')->rows;
        $body_saleschannel = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/saleschannel')->rows;

        if($Setting->Organization != null){
            $body_organization = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/organization/" . $Setting->Organization)->rows;
        } else {
            $body_organization = $Client->get("https://online.moysklad.ru/api/remap/1.2/entity/organization/")->rows;
        }



        return view('web.Setting.Automation', [

            'arr_meta'=> $body_meta,
            'arr_project'=> $body_project,
            'arr_saleschannel'=> $body_saleschannel,
            'arr_organization'=> $body_organization,

            "message"=> $message,
            "class"=> $class,

            "accountId"=> $accountId,
            "isAdmin" => $isAdmin,
        ]);
    }
}
