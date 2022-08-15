<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\GuzzleClient\ClientMC;
use Faker\Provider\File;
use Illuminate\Http\Request;

class postController extends Controller
{
    public function postClint(Request $request, $accountId){
        $Setting = new getSettingVendorController($accountId);
        $TokenMC = $Setting->TokenMoySklad;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";

        $Clint = new ClientMC($url, $TokenMC);

       $this->loginfo("request", $request);

        $fields = $request->validate([
            'displayName' => 'required|string',
            'participant' => 'required',
            'phone' => 'required|string',
            'email' => 'required|string',
        ]);

        $body = [
            "name" => $fields["displayName"],
            "phone" => $fields["phone"],
            "email" => $fields["email"],
            "externalCode" => $fields["participant"]->id,
        ];



        $Clint->requestPost($body);


    }

    function loginfo($name, $msg) {
        global $dirRoot;
        $logDir =  public_path();
        @mkdir($logDir);
        file_put_contents($logDir . '/log.txt', date(DATE_W3C) . ' [' . $name . '] '. $msg . "\n", FILE_APPEND);
    }

}
