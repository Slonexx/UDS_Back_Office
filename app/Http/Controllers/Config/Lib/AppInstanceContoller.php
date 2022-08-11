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

    }

}
