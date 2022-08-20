<?php

namespace App\Services\Settings;

use App\Http\Controllers\Config\getSettingVendorController;

class StateOrderSettings
{

    private SettingsService $settingsService;

    /**
     * @param SettingsService $settingsService
     */
    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }


    public function getStatusName($accountId, $statusFrom)
    {
        $status = null;

        //Настройка статусов нужны тут
        //$settings = $this->settingsService->getSettings();

        //$currSetting = null;

        /*foreach($settings as $setting){
            if($setting->accountId == $accountId){
                $currSetting = $setting;
                break;
            }
        }*/

        $currSetting = new getSettingVendorController($accountId);

        if($currSetting != null){
            switch ($statusFrom) {
                case 'COMPLETED':
                    $status = $currSetting->COMPLETED;
                    break;
                case 'DELETED':
                    $status = $currSetting->DELETED;
                    break;
            }
        }

        /*switch ($statusFrom) {
            case 'NEW':
                $status = "Подтвержден";
                break;
            case 'COMPLETED':
                $status = "Доставлен";
                break;
            case 'DELETED':
                $status = "Отменен";
                break;
        }*/

        return $status;
    }
}
