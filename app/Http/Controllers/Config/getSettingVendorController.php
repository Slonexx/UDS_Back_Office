<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Config\Lib\AppInstanceContoller;
use App\Http\Controllers\Config\Lib\cfg;
use App\Http\Controllers\Config\Lib\VendorApiController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class getSettingVendorController extends Controller
{
    var $appId;
    var $accountId;
    var $TokenMoySklad;
    var $companyId;
    var $TokenUDS;
    var $ProductFolder;
    var $UpdateProduct;
    var $Store;

    var $creatDocument;
    var $Organization;
    var $PaymentDocument;
    var $Document;
    var $PaymentAccount;

    var $Saleschannel;
    var $Project;

    var $NEW;
    var $COMPLETED;
    var $DELETED;

    public function __construct($accountId)
    {
        $json = AppInstanceContoller::loadApp($accountId);

        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->companyId = $json->companyId;
        $this->TokenUDS = $json->TokenUDS;
        $this->ProductFolder = $json->ProductFolder;
        $this->UpdateProduct = $json->UpdateProduct;
        $this->Store = $json->Store;

        $this->creatDocument = $json->creatDocument;
        $this->Organization = $json->Organization;
        $this->PaymentDocument = $json->PaymentDocument;
        $this->Document = $json->Document;
        $this->PaymentAccount = $json->PaymentAccount;

        $this->Saleschannel = $json->Saleschannel;
        $this->Project = $json->Project;

        $this->NEW = $json->NEW;
        $this->COMPLETED = $json->COMPLETED;
        $this->DELETED = $json->DELETED;

        return $json;

    }



}
