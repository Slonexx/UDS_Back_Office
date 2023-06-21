<?php

namespace App\Http\Controllers\BD;

use App\Http\Controllers\Controller;
use App\Models\Automation_new_update_MODEL;
use App\Models\SettingMain;
use Illuminate\Http\Request;

class update extends Controller
{
    public function SettingMainUpdate($accountId, $TokenMS, $companyId, $TokenUDS, $ProductFolder, $UpdateProduct, $Store, $productHidden){
        $SettingMain_update = SettingMain::query()->where('accountId', $accountId);
        $SettingMain_update->update([
            'TokenMoySklad' => $TokenMS,
            'companyId' => $companyId,
            'TokenUDS' => $TokenUDS,
            'ProductFolder' => $ProductFolder,
            'UpdateProduct' => $UpdateProduct,
            'Store' => $Store,
            'hiddenProduct' => $productHidden,
        ]);
    }

    public function AutomationUpdate($accountId, $activateAutomation, $statusAutomation, $projectAutomation,
                                     $saleschannelAutomation, $automationDocument, $add_automationStore,
                                     $add_automationPaymentDocument, $documentAutomation  ){

        $Automation_new_update_MODEL = Automation_new_update_MODEL::query()->where('accountId', $accountId);
        $Automation_new_update_MODEL->update([
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
