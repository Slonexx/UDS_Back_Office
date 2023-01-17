<?php

namespace App\Http\Controllers\Web;

use App\Components\UdsClient;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\GuzzleClient\ClientMC;
use App\Http\Controllers\mainURL;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class indexController extends Controller
{

    public function index(Request $request): \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\Foundation\Application|\Illuminate\Http\RedirectResponse
    {
        $contextKey = $request->contextKey;
        if ($contextKey == null) {
            return view("main.dump");
        }

        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        $isAdmin = $employee->permissions->admin->view;

        return redirect()->route('indexMain', [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ] );

    }

    public function show($accountId, $isAdmin): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        return view("web.index" , [
            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
            ] );
    }

    public function CheckSave(Request $request, $accountId){

        $Setting = new getSettingVendorController($accountId);

        dd($Setting);

    }

    public function counterparty(Request $request): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Contracts\Foundation\Application
    {
        $baseURL = new mainURL();

        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;

        $isAdmin = $employee->permissions->admin->view;

        $entity = 'counterparty';

        $getObjectUrl = $baseURL->url_host() . "CounterpartyObject/$accountId/$entity/";

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

    public function CustomerOrderEdit(Request $request){
        $baseURL = new mainURL();

        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        //$accountId = "1dd5bd55-d141-11ec-0a80-055600047495";
        //$isAdmin = $employee->permissions->admin->view;

        $entity = 'customerorder';

        $getObjectUrl = $baseURL->url_host() . "CustomerOrderEditObject/$accountId/$entity/";


        return view( 'widgets.CustomerOrderEdit', [
            'accountId' => $accountId,
            'cashier_id' => $employee->id,
            //'cashier_id' => "Сергей",
            //'cashier_name' => "e793faeb-e63a-11ec-0a80-0b4800079eb3",
            'cashier_name' => $employee->name,
            'getObjectUrl' => $getObjectUrl,
        ] );
    }

    public function DemandEdit(Request $request){
        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        $getObjectUrl = "https://smartuds.kz/Demand/$accountId/demand/";


        return view( 'widgets.Demand', [
            'accountId' => $accountId,
            'cashier_id' => $employee->id,
            'cashier_name' => $employee->name,
            'getObjectUrl' => $getObjectUrl,
        ] );
    }

    public function SalesreturnEdit(Request $request){
        $contextKey = $request->contextKey;
        $vendorAPI = new VendorApiController();
        $employee = $vendorAPI->context($contextKey);
        $accountId = $employee->accountId;
        $getObjectUrl = "https://smartuds.kz/Salesreturn/$accountId/salesreturn/";


        return view( 'widgets.Salesreturn', [
            'accountId' => $accountId,
            'getObjectUrl' => $getObjectUrl,
        ] );
    }
}
