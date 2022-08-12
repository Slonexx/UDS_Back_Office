<?php

namespace App\Http\Controllers\Web;

use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Throwable;

class RewardController extends Controller
{
    public function Accrue(Request $request, $accountId, $points, $participants){
        $url = "https://api.uds.app/partner/v2/operations/reward";
        $Setting = new getSettingVendorController($accountId);

        $body = [
            "points" => $points,
            "comment" => "",
            "silent" => false,
            "participants" => [ $participants ],
        ];

        $UDSClint = new UdsClient($Setting->companyId,$Setting->TokenUDS);
        try {
            $resultBOdy = $UDSClint->post($url,$body);
            return response()->json(
                "200",201);
        } catch (Throwable $exception){
            return response()->json(
                "400",201);
        }

    }
}
