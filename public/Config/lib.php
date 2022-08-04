<?php

class AppInstanceContoller {

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

    function deleteAnway(){
        $url = 'https://smartkaspi.kz/api/DeleteVendorApi/'.$this->appId.'/'.$this->accountId;
        $result = file_get_contents($url);
        $this->loginfo('Удлаение', $result);
    }

    private function filename() {
        return self::buildFilename($this->appId, $this->accountId);
    }

    private static function buildFilename($appId, $accountId) {
        return $GLOBALS['dirRoot'] . "data/$appId.$accountId.json";
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

    function loginfo($name, $msg) {
        global $dirRoot;
        $logDir = $dirRoot . 'logs';
        @mkdir($logDir);
        file_put_contents($logDir . '/log.txt', date(DATE_W3C) . ' [' . $name . '] '. $msg . "\n", FILE_APPEND);
    }


}
