<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\BackEnd\BDController;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeleteVendorApiController extends Controller
{
    public function Delete($accountId){
        $Setting = new getSettingVendorController($accountId);
        $cfg = new cfg();
        $bd = new BDController();
        try {
            $bd->deleteCounterparty($Setting->TokenMoySklad);
            $path = public_path().'/Config/data/'.$cfg->appId.".".$accountId.'.json';
            unlink($path);
        } catch (\Exception $e) {
            $bd->errorLog($accountId, $e->getMessage());
        }


    }
}
