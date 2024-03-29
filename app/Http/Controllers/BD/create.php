<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\Automation_new_update_MODEL;
use App\Models\SettingMain;
use Illuminate\Http\Request;

class create extends Controller
{
    public function SettingMainCreate($accountId, $TokenMS, $companyId, $TokenUDS, $ProductFolder, $UpdateProduct, $Store, $productHidden){
        SettingMain::create([
            'accountId' => $accountId,
            'TokenMoySklad' => $TokenMS,
            'companyId' => $companyId,
            'TokenUDS' => $TokenUDS,
            'ProductFolder' => $ProductFolder,
            'UpdateProduct' => $UpdateProduct,
            'Store' => $Store,
            'productHidden' => $productHidden,
        ]);
    }


    public function AutomationCreate($accountId, $activateAutomation, $statusAutomation, $projectAutomation,
                                     $saleschannelAutomation, $automationDocument, $add_automationStore,
                                     $add_automationPaymentDocument, $documentAutomation  ){

        Automation_new_update_MODEL::create([
            'accountId' => $accountId,

            'activateAutomation' => $activateAutomation,
            'statusAutomation' => $statusAutomation,
            'projectAutomation' => $projectAutomation,
            'saleschannelAutomation' => $saleschannelAutomation,

            'automationDocument' => $automationDocument,
            'add_automationStore' => $add_automationStore,
            'add_automationPaymentDocument' => $add_automationPaymentDocument,
            'documentAutomation' => $documentAutomation,
        ]);
    }

}
