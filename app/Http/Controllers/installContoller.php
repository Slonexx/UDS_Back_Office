<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\getSettingVendorController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class installContoller extends Controller
{
    public function install($accountId): void
    {
        try {
            $Setting = new getSettingVendorController($accountId);

            $client = new Client(['base_uri' => 'https://smartuds.kz/api/']);
            $client->post('attributes',[
                'headers'=> ['Accept' => 'application/json'],
                'form_params' => [
                    "tokenMs" => $Setting->TokenMoySklad,
                    "accountId" => $accountId
                ]
            ]);
        } catch (\Exception $ee) {
            $bd = new BDController();
            $bd->errorLog($accountId, $ee->getMessage());
        } catch (GuzzleException $e) {
            $bd = new BDController();
            $bd->errorLog($accountId, $e->getMessage());
        }
    }
}
