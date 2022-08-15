<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\GuzzleClient\ClientMC;
use Faker\Provider\File;
use Illuminate\Http\Request;
use Throwable;

class postController extends Controller
{
    public function postClint(Request $request, $accountId){
        $Setting = new getSettingVendorController($accountId);
        $TokenMC = $Setting->TokenMoySklad;

        $url = "https://online.moysklad.ru/api/remap/1.2/entity/counterparty";

        $Clint = new ClientMC($url, $TokenMC);

        $participant = $request->participant;

        $email = $this->ClintNullable($request->email);
        //dd($email);


        $body = [
            "name" => $request->displayName,
            "phone" => $request->phone,
            "email" => $email,
            "externalCode" => (string) $participant['id'],
        ];
        try {
            $Clint->requestPost($body);
        } catch (Throwable $exception){
            dd($exception);
        }



    }

    public function ClintNullable($item){
        if ($item == null){
            return '';
        } else {
            return $item;
        }
    }
}
