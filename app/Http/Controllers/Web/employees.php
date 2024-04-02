<?php

namespace App\Http\Controllers\Web;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class employees extends Controller
{
    public function index(Request $request, $accountId, $isAdmin){
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
        $url_employee = 'https://api.moysklad.ru/api/remap/1.2/entity/employee';
        $Client = new MsClient($TokenMoySklad);
        $Body_employee = $Client->get($url_employee)->rows;
        $security = [];

        foreach ($Body_employee as $item){
            $temp[$item->id] = $Client->get($url_employee.'/'.$item->id.'/security');
            if (isset($temp[$item->id]->role)){
                $security[$item->id] = mb_substr ($temp[$item->id]->role->meta->href, 53);
            } else {
                $security[$item->id] = 'cashier';
            }
        }



        return view('web.Setting.employees', [
            "accountId"=> $accountId,
            "isAdmin" => $isAdmin,

            'employee' => $Body_employee,
            'security' => $security,

            "message" => $request->message ?? '',
            "class_message" => $request->class_message ?? 'is-info',
        ]);
    }
}
