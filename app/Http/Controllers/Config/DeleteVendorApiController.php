<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DeleteVendorApiController extends Controller
{
    public function Delete($appId, $accountId){

        $path = public_path().'/Config/data/'.$appId.".".$accountId.'.json';
        unlink($path);

    }
}
