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

        $uid = $employee->uid;
        $fio = $employee->shortFio;

        $Setting = new getSettingVendorController($accountId);

        $entity = 'counterparty';

        $getObjectUrl = $cfg->appBaseUrl . "widgets/get-object.php?accountId=$accountId&entity=$entity&objectId=";

        dd($request);



        return view( 'widgets.counterparty', [
            'accountId' => $accountId,
            'uid' => $uid,
            'fio' => $fio,
            'getObjectUrl' => $getObjectUrl,
            ] );
    }

}
