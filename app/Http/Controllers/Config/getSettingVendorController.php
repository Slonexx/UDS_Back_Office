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
    var $WAITING_PAYMENT;

    public function __construct($accountId)
    {

        $cfg = new cfg();

        $appId = $cfg->appId;
        $json = AppInstanceContoller::loadApp($appId, $accountId);

        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->companyId = $json->companyId;
        $this->TokenUDS = $json->TokenUDS;
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
        $this->WAITING_PAYMENT = $json->WAITING_PAYMENT;

        return $json;

    }

    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->Store;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @return mixed
     */
    public function getTokenMoySklad()
    {
        return $this->TokenMoySklad;
    }

    /**
     * @return mixed
     */
    public function getTokenKaspi()
    {
        return $this->TokenKaspi;
    }

    /**
     * @return mixed
     */
    public function getOrganization()
    {
        return $this->Organization;
    }

    /**
     * @return mixed
     */
    public function getPaymentDocument()
    {
        return $this->PaymentDocument;
    }

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->Document;
    }

    /**
     * @return mixed
     */
    public function getPaymentAccount()
    {
        return $this->PaymentAccount;
    }

    /**
     * @return mixed
     */
    public function getSaleschannel()
    {
        return $this->Saleschannel;
    }

    /**
     * @return mixed
     */
    public function getProject()
    {
        return $this->Project;
    }

    /**
     * @return mixed
     */
    public function getCheckCreatProduct()
    {
        return $this->CheckCreatProduct;
    }

    /**
     * @return mixed
     */
    public function getAPPROVEDBYBANK()
    {
        return $this->APPROVED_BY_BANK;
    }

    /**
     * @return mixed
     */
    public function getACCEPTEDBYMERCHANT()
    {
        return $this->ACCEPTED_BY_MERCHANT;
    }

    /**
     * @return mixed
     */
    public function getCOMPLETED()
    {
        return $this->COMPLETED;
    }

    /**
     * @return mixed
     */
    public function getCANCELLED()
    {
        return $this->CANCELLED;
    }

    /**
     * @return mixed
     */
    public function getRETURNED()
    {
        return $this->RETURNED;
    }



}
