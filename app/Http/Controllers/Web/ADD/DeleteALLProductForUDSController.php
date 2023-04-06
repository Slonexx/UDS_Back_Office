<?php

namespace App\Http\Controllers\Web\ADD;

use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Services\product\ProductCreateUdsService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;

class DeleteALLProductForUDSController extends Controller
{

    public function DeleteALLProductForUDSController(Request $request, $as, $accountId): \Illuminate\Http\JsonResponse
    {
        if ($as == "p330538"){
            $result = [];
            $setting = new getSettingVendorController($accountId);
            $UDS = app(ProductCreateUdsService::class)->getUdsCheck($setting->companyId, $setting->TokenUDS, $accountId);
            $Client = new UdsClient($setting->companyId, $setting->TokenUDS);
            if ($UDS['productIds']!=[]){
                foreach ($UDS['productIds'] as $item){
                    try {
                        $Client->delete("https://api.uds.app/partner/v2/goods/".$item);
                        $result[] = "Удаленно = ".$item;
                    } catch (GuzzleException $exception){
                        $result[] = "Не удалось удалить = ".$item;
                        continue;
                    }
                }
            }
            return response()->json($result);
        } else return response()->json([],404);

    }
}
