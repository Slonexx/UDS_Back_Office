<?php

namespace App\Http\Controllers\Web\POST;

use App\Components\MsClient;
use App\Http\Controllers\BD\create;
use App\Http\Controllers\BD\update;
use App\Http\Controllers\Config\getSettingVendorController;
use App\Http\Controllers\Controller;
use App\Models\Automation_new_update_MODEL;
use GuzzleHttp\Exception\BadResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class postAutomationController extends Controller
{
    public function postSettingAdd(Request $request,  $accountId, $isAdmin): RedirectResponse
    {
       //dd($request->all());
        $Setting = new getSettingVendorController($accountId);

       $activateAutomation = $request->activateAutomation;
       $statusAutomation = $request->statusAutomation;
       $projectAutomation = $request->projectAutomation;
       $saleschannelAutomation = $request->saleschannelAutomation;

       $automationDocument = $request->automationDocument;
       $add_automationStore = $request->add_automationStore;
       $add_automationPaymentDocument = $request->add_automationPaymentDocument;
       $documentAutomation = $request->documentAutomation;

        if ($activateAutomation == 0){
            $statusAutomation = null;
            $projectAutomation = null;
            $saleschannelAutomation = null;

            $add_automationStore = null;
            $add_automationPaymentDocument = null;
        }

       if ($automationDocument == 1) {
           $add_automationStore = null;
           $add_automationPaymentDocument = null;
       }

       $find = Automation_new_update_MODEL::query()->where('accountId', $accountId)->get()->all();
       if ($find == []){
           $create = new create();
           $create->AutomationCreate($accountId, $activateAutomation, $statusAutomation,
               $projectAutomation, $saleschannelAutomation, $automationDocument,
               $add_automationStore, $add_automationPaymentDocument, $documentAutomation );
       } else {
           $update = new update();
           $update->AutomationUpdate($accountId, $activateAutomation, $statusAutomation,
               $projectAutomation, $saleschannelAutomation, $automationDocument,
               $add_automationStore, $add_automationPaymentDocument, $documentAutomation );
       }

        try {
            $Client = new MsClient($Setting->TokenMoySklad);
            $url_check ='https://smartuds.kz/api/webhook/customerorder' ;
            $Webhook_check = true;
            $Webhook_body = $Client->get('https://online.moysklad.ru/api/remap/1.2/entity/webhook/')->rows;
            if ($Webhook_body != []){
                foreach ($Webhook_body as $item){
                    if ($item->url == $url_check){
                        $Webhook_check = false;
                    }
                }
            }
            if ($Webhook_check) {
                if ($request->documentAutomation == 0){
                    $entity = 'customerorder';
                } else  $entity = 'demand';
                $Client->post('https://online.moysklad.ru/api/remap/1.2/entity/webhook/', [
                    'url' => $url_check,
                    'action' => "UPDATE",
                    'entityType' => $entity,
                    'diffType' => "FIELDS",
                ]);
            }


            $message = "Настройки сохранились";
        } catch (BadResponseException $e){
            $message = json_decode($e->getResponse()->getBody()->getContents())->errors[0]->error;
        }


        return  redirect()->route('getAutomation', [
            'message' => $message,

            'accountId' => $accountId,
            'isAdmin' => $isAdmin,
        ]);
    }
}
