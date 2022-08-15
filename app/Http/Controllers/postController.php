<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\GuzzleClient\ClientMC;
use Illuminate\Http\Request;

class postController extends Controller
{
    public function postClint(Request $request, $accountId){
        $Setting = new getSettingVendorController($accountId);
        $TokenMC = $Setting->TokenMoySklad;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";

        $Clint = new ClientMC($url, $TokenMC);

        /*$tmp = json_decode($request);

        dd($tmp);*/

        $body = [
            "name" => $request->displayName,
        ];



        $Clint->requestPost($body);


    }
}
