<?php

class AppInstanceContoller {

    const UNKNOWN = 0;
    const SETTINGS_REQUIRED = 1;
    const ACTIVATED = 100;

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

    function deleteAnyway(){
        $url = 'https://smartuds.kz/DeleteVendorApi/'.$this->appId.'/'.$this->accountId;
        $result = file_get_contents($url);
    }

    private function filename() {
        return self::buildFilename($this->appId, $this->accountId);
    }

    private static function buildFilename($appId, $accountId) {
        $dirRoot = public_path().'/Config/';
        return $dirRoot . "data/$appId.$accountId.json";
    }

    static function loadApp($accountId): AppInstanceContoller {
        return self::load(cfg()->appId, $accountId);
    }

    static function load($appId, $accountId): AppInstanceContoller {
        $data = @file_get_contents(self::buildFilename($appId, $accountId));
        if ($data === false) {
            $app = new AppInstanceContoller($appId, $accountId);
        } else {
            $unser = json_encode( unserialize($data) );
            $app =  json_decode($unser);
        }

        $AppInstance = new AppInstanceContoller($app->appId, $app->accountId);
        $AppInstance->Pasrs($app);

        $GLOBALS['currentAppInstance'] = $AppInstance;
        return $AppInstance;
    }

    public function Pasrs($json){
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
    }

}
