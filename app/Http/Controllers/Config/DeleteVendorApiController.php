<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeleteVendorApiController extends Controller
{
    public function Delete($accountId){
        $cfg = new cfg();
        $path = public_path().'/Config/data/'.$cfg->appId.".".$accountId.'.json';
        unlink($path);

    }
}
