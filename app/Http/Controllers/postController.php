<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Config\getSettingVendorController;
use Illuminate\Http\Request;

class postController extends Controller
{
    public function postClint(Request $request, $accountId){
        $Setting = new getSettingVendorController($accountId);
        $TokenMC = $Setting->TokenMoySklad;



    }
}
