<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class cfg extends Controller
{
    public $appId;
    public $appUid;
    public $secretKey;
    public $appBaseUrl;
    public $moyskladVendorApiEndpointUrl;
    public $moyskladJsonApiEndpointUrl;


    public function __construct()
    {
        $cfg = new AppConfigController( require(public_path().'/Config'.'/config.php') );

        $this->appId = $cfg->appId;
        $this->appUid = $cfg->appUid;
        $this->secretKey = $cfg->secretKey;
        $this->appBaseUrl = $cfg->appBaseUrl;
        $this->moyskladVendorApiEndpointUrl = $cfg->moyskladVendorApiEndpointUrl;
        $this->moyskladJsonApiEndpointUrl = $cfg->moyskladJsonApiEndpointUrl;
    }


}
