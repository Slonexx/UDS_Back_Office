<?php

namespace App\Http\Controllers\Web;

use App\Components\UdsClient;
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
        $isAdmin = $employee->permissions->admin->view;


        return redirect()->route('indexMain', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ] );

    }

    public function show($accountId, $isAdmin){
        return view("web.index" , [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            ] );
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

        $isAdmin = $employee->permissions->admin->view;

        $entity = 'counterparty';

        $getObjectUrl = $cfg->appBaseUrl . "CounterpartyObject/$accountId/$entity/";

        if ($isAdmin == "NO"){
            return view( 'widgets.counterparty', [
                'accountId' => $accountId,
                'getObjectUrl' => $getObjectUrl,
                'admin' => "NO",
            ] );
        }

        return view( 'widgets.counterparty', [
            'accountId' => $accountId,

            'getObjectUrl' => $getObjectUrl,
            ] );
    }

    public function CounterpartyObject(Request $request, $accountId, $entity, $objectId){

        $UDSURL = "https://api.uds.app/partner/v2/customers/";

        $cfg = new cfg();
        $Setting = new getSettingVendorController($accountId);


        $urlCounterparty = $cfg->moyskladJsonApiEndpointUrl."/entity/$entity/$objectId";
        $BodyCounterparty = new ClientMC($urlCounterparty, $Setting->TokenMoySklad);

        $externalCode =  $BodyCounterparty->requestGet()->externalCode;



        $body = new UdsClient($Setting->companyId, $Setting->TokenUDS);
        $last = $body->get($UDSURL.$externalCode);


        return response()->json(
            $last,201);

    }



}
