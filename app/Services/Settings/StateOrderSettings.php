<?php

namespace App\Services\Settings;

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
        }

        if($currSetting != null){

            switch ($statusFrom) {
                case 'COMPLETED':
                    $status = "";
                    break;
                case 'DELETED':
                    $status = "";
                    break;
            }

        }*/

        switch ($statusFrom) {
            case 'NEW':
                $status = "Подтвержден";
                break;
            case 'COMPLETED':
                $status = "Доставлен";
                break;
            case 'DELETED':
                $status = "Отменен";
                break;
        }

        return $status;
    }
}
