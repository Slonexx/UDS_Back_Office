<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\Automation_new_update_MODEL;
use App\Models\SettingMain;
use Illuminate\Http\Request;

class create extends Controller
{
    public function SettingMainCreate($accountId, $TokenMS, $companyId, $TokenUDS, $ProductFolder, $UpdateProduct, $Store){
        SettingMain::create([
            'accountId' => $accountId,
            'TokenMoySklad' => $TokenMS,
            'companyId' => $companyId,
            'TokenUDS' => $TokenUDS,
            'ProductFolder' => $ProductFolder,
            'UpdateProduct' => $UpdateProduct,
            'Store' => $Store,
        ]);
    }


    public function AutomationCreate($accountId, $activateAutomation, $statusAutomation, $projectAutomation,
                                     $saleschannelAutomation, $automationDocument, $add_automationOrganization, $add_automationStore,
                                     $add_automationPaymentDocument, $add_saleschannelAutomation, $add_projectAutomation ){

        Automation_new_update_MODEL::create([
            'accountId' => $accountId,

            'activateAutomation' => $activateAutomation,
            'statusAutomation' => $statusAutomation,
            'projectAutomation' => $projectAutomation,
            'saleschannelAutomation' => $saleschannelAutomation,

            'automationDocument' => $automationDocument,
            'add_automationOrganization' => $add_automationOrganization,
            'add_automationStore' => $add_automationStore,
            'add_automationPaymentDocument' => $add_automationPaymentDocument,
            'add_saleschannelAutomation' => $add_saleschannelAutomation,
            'add_projectAutomation' => $add_projectAutomation,
        ]);
    }

}
