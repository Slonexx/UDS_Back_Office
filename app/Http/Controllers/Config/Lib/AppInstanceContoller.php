<?php

namespace App\Http\Controllers\Config\Lib;

use App\Http\Controllers\Controller;
use Doctrine\Instantiator\Exception\InvalidArgumentException;


class AppInstanceContoller
{
    const UNKNOWN = 0;
    const SETTINGS_REQUIRED = 1;
    const ACTIVATED = 100;

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

    var $status = AppInstanceContoller::UNKNOWN;

    static function get(): AppInstanceContoller {
        $app = $GLOBALS['currentAppInstance'];
        if (!$app) {
            throw new InvalidArgumentException("There is no current app instance context");
        }
        return $app;
    }

    public function __construct($appId, $accountId)
    {
        $this->appId = $appId;
        $this->accountId = $accountId;
    }

    function getStatusName() {
        switch ($this->status) {
            case self::SETTINGS_REQUIRED:
                return 'SettingsRequired';
            case self::ACTIVATED:
                return 'Activated';
        }
        return null;
    }

    function persist() {
        @mkdir('data');
        file_put_contents($this->filename(), serialize($this));
    }

    function delete() {
        @unlink($this->filename());
    }

    private function filename() {
        return self::buildFilename($this->appId, $this->accountId);
    }

    private static function buildFilename($appId, $accountId) {
        $dir = public_path().'/Config/';
        return $dir . "data/$appId.$accountId.json";
    }

    static function loadApp($appId, $accountId): AppInstanceContoller {
        return self::load($appId, $accountId);
    }

    static function load($appId, $accountId): AppInstanceContoller {
        $data = @file_get_contents(self::buildFilename($appId, $accountId));
        if ($data === false) {
            $app = new AppInstanceContoller($appId, $accountId);
        } else {
            $unser = json_encode( unserialize($data) );
            $app =  json_decode($unser);
        }

        $_SESSION['currentAppInstance'] = $data;

        $AppInstance = new AppInstanceContoller($app->appId, $app->accountId);

        $AppInstance->setAppToClassAppInstance($app);

        //dd($AppInstance);

        return $AppInstance;
    }

    public function setAppToClassAppInstance($json){
        $this->appId = $json->appId;
        $this->accountId = $json->accountId;
        $this->TokenMoySklad = $json->TokenMoySklad;
        $this->TokenKaspi = $json->TokenKaspi;
        $this->Organization = $json->Organization;
        $this->PaymentDocument = $json->PaymentDocument;
        $this->Saleschannel = $json->Saleschannel;
        $this->Project = $json->Project;
        $this->Document = $json->Document;
        $this->PaymentAccount = $json->PaymentAccount;
        $this->CheckCreatProduct = $json->CheckCreatProduct;
        $this->Store = $json->Store;
        $this->APPROVED_BY_BANK = $json->APPROVED_BY_BANK;
        $this->ACCEPTED_BY_MERCHANT = $json->ACCEPTED_BY_MERCHANT;
        $this->COMPLETED = $json->COMPLETED;
        $this->CANCELLED = $json->CANCELLED;
        $this->RETURNED = $json->RETURNED;
    }

}
