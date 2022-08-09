<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class indexController extends Controller
{

    public function index(Request $request){

        session_start();

        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        return redirect()->route('indexMain', ['accountId' => $accountId] );

    }

    public function show($accountId){
        return view("web.index" , ['accountId' => $accountId] );
    }

    public function CheckSave(Request $request, $accountId){

        $Setting = new getSettingVendorController($accountId);

        dd($Setting);

    }

    public function counterparty(Request $request){
        $cfg = new cfg();

        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        $entity = 'counterparty';

        $getObjectUrl = $cfg->appBaseUrl . "CounterpartyObject?accountId=$accountId&entity=$entity&objectId=";


        return view( 'widgets.counterparty', [
            'accountId' => $accountId,

            'getObjectUrl' => $getObjectUrl,
            ] );
    }

    public function CounterpartyObject(Request $request){

        $json = [
            "entity" => $request->entity,
            "objectId" => $request->objectId,
            "accountId" => $request->accountId,
        ];

        echo json_encode($json);
    }



}
