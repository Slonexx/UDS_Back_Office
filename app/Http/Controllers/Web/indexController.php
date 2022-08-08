<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
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
        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        dd($contextKey);
        return route('CounterpartyShow', [ 'accountId' => $accountId ]);
    }

    public function counterpartyShow(Request $request, $accountId){

        $contextName = 'COUNTERPARTY-WIDGET';
        $entity = 'counterparty';
        $cfg = new cfg();
        $getObjectUrl = $cfg->appBaseUrl . "/widgets/get-object.php?accountId=$accountId&entity=$entity&objectId=";


        return view( 'web.counterparty', ['accountId' => $accountId] );
    }
}
