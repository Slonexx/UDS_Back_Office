<?php

namespace App\Http\Controllers\Web;

use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\Request;
use Throwable;

class RewardController extends Controller
{
    public function Accrue($accountId, $points, $participants): \Illuminate\Http\JsonResponse
    {
        $Setting = new getSettingVendorController($accountId);

        $body = [
            "points" => round($points, 2),
            "comment" => "",
            "silent" => false,
            "participants" => [$participants],
        ];

        try {
            $this->sendRequest($Setting, $body);
            return response()->json(['Bool' => true]);
        } catch (BadResponseException) {
            return response()->json(['Bool' => false]);
        }
    }

    public function Cancellation($accountId, $points, $participants): \Illuminate\Http\JsonResponse
    {
        $Setting = new getSettingVendorController($accountId);

        $points = -$points;

        $body = [
            "points" => $points,
            "comment" => "",
            "silent" => true,
            "participants" => [$participants],
        ];

        try {
            $this->sendRequest($Setting, $body);
            return response()->json(['Bool' => true]);
        } catch (BadResponseException) {
            return response()->json(['Bool' => false]);
        }
    }

    private function sendRequest(getSettingVendorController $Setting, array $body): void
    {
        $url = "https://api.uds.app/partner/v2/operations/reward";
        $UDSClint = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $UDSClint->post($url, $body);
    }
}
