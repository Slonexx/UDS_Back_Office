<?php

namespace App\Http\Controllers\Web;

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
        $url_employee = 'https://online.moysklad.ru/api/remap/1.2/entity/employee';
        $Client = new ClientMC($url_employee, $TokenMoySklad);
        $Body_employee = $Client->requestGet()->rows;
        $security = [];

        $urls = [];
        foreach ($Body_employee as $id=>$item){
            $url_security = $url_employee.'/'.$item->id.'/security';
            $urls [] = $url_security;
        }

        $pools = function (Pool $pool) use ($urls,$TokenMoySklad){
            foreach ($urls as $url){
                $arrPools [] = $pool->withToken($TokenMoySklad)->get($url);
            }
            return $arrPools;
        };

        $responses = Http::pool($pools);
        $count = 0;
        foreach ($Body_employee as $id=>$item){
            if ( isset($responses[$count]->object()->role) ){
                $Body_security = $responses[$count]->object()->role;
                $security[$item->id] = mb_substr ($Body_security->meta->href, 53);
            } else {
                $security[$item->id] = 'cashier';
            }

            $count++;
        }

        return view('web.Setting.employees', [
            "accountId"=> $accountId,
            "isAdmin" => $isAdmin,

            'employee' => $Body_employee,
            'security' => $security,

        ]);
    }
}
