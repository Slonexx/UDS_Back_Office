<?php

namespace App\Http\Controllers\Web\ADD;

use App\Components\MsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Http\Controllers\mainURL;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function CountProduct(Request $request): \Illuminate\Http\JsonResponse
    {
        $Setting = new getSettingVendorController($request->accountId);
        $folderName = $request->folderName;
        $Client = new MsClient($Setting->TokenMoySklad);

        $url = app(mainURL::class)->url_ms() . 'product';


        try {
            if ($folderName == 'Корневая папка'){
                $Body = $Client->get($url);
            } else {
                $Body = $Client->get($url.'?filter=pathName~'.$folderName);
            }

            $result = [
                'StatusCode' => 200,
                'Body' => $Body->meta->size,
            ];
        } catch (ClientException $exception){
            $result = [
                'StatusCode' => $exception->getCode(),
                'Body' => $exception->getMessage(),
            ];
        }
        return response()->json($result);
    }
}
