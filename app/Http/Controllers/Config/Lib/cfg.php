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
        $this->appId = '1e0e2609-2839-4667-a2f7-964f939c66d3';
        $this->appUid = 'udsbackoffice.smartinnovations';
        $this->secretKey = "yrLVLeOAiAmtrI5K1HTCGano7VybaSVjPtqWBMnia53iStLaX8KXCET4VvM6INc2Nbz1NbeSTHv5KiCTe3UcEfok8gv8sFDkEqcBi9krAnCy7Rt1y1dIcbcaZPLxKfG5";
        $this->appBaseUrl = 'https://smartuds.kz/';
        $this->moyskladVendorApiEndpointUrl = 'https://apps-api.moysklad.ru/api/vendor/1.0';
        $this->moyskladJsonApiEndpointUrl = 'https://api.moysklad.ru/api/remap/1.2';
    }


}
