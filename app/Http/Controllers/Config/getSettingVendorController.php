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
    var $TokenKaspi;
    var $Organization;
    var $PaymentDocument;
    var $Document;
    var $PaymentAccount;
    var $Saleschannel;
    var $Project;
    var $CheckCreatProduct;
    var $Store;
    var $APPROVED_BY_BANK;
    var $ACCEPTED_BY_MERCHANT;
    var $COMPLETED;
    var $CANCELLED;
    var $RETURNED;
    var $APP;

    public function __construct($accountId)
    {

        $cfg = new cfg();

        $appId = $cfg->appId;
        $app = AppInstanceContoller::loadApp($appId, $accountId);

        $this->appId = $app->appId;
        $this->accountId = $app->accountId;
        $this->TokenMoySklad = $app->TokenMoySklad;
        $this->TokenKaspi = $app->TokenKaspi;
        $this->Organization = $app->Organization;
        $this->PaymentDocument = $app->PaymentDocument;
        $this->Document = $app->Document;
        $this->PaymentAccount = $app->PaymentAccount;
        $this->Saleschannel = $app->Saleschannel;
        $this->Project = $app->Project;
        $this->CheckCreatProduct = $app->CheckCreatProduct;
        $this->Store = $app->Store;
        $this->APPROVED_BY_BANK = $app->APPROVED_BY_BANK;
        $this->ACCEPTED_BY_MERCHANT = $app->ACCEPTED_BY_MERCHANT;
        $this->COMPLETED = $app->COMPLETED;
        $this->CANCELLED = $app->CANCELLED;
        $this->RETURNED = $app->RETURNED;
        $this->APP = $app;

        return $app;

    }

    /**
     * @return mixed
     */
    public function getStore()
    {
        return $this->Store;
    }

    public function getSetting(){
    return $this->APP;
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
