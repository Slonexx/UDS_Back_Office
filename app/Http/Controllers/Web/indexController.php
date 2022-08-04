<?php

namespace App\Http\Controllers\Web;

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
}
